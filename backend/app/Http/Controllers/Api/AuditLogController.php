<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->baseQuery();

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("u.name", "like", "%{$search}%")
                  ->orWhere("a.module", "like", "%{$search}%")
                  ->orWhere("a.action", "like", "%{$search}%")
                  ->orWhere("a.record_code", "like", "%{$search}%")
                  ->orWhere("a.remarks", "like", "%{$search}%");
            });
        }
        if ($request->filled("role"))     $query->where("r.name", $request->role);
        if ($request->filled("module"))   $query->where("a.module", $request->module);
        if ($request->filled("action"))   $query->whereIn("a.action", explode(",", $request->action));
        if ($request->filled("date_from")) $query->whereDate("a.performed_at", ">=", $request->date_from);
        if ($request->filled("date_to"))   $query->whereDate("a.performed_at", "<=", $request->date_to);

        $logs  = $query->orderBy("a.performed_at", "desc")->paginate($request->get("per_page", 15));
        $stats = $this->getStats();

        return response()->json(["logs" => $logs, "stats" => $stats]);
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }

    public function modules()
    {
        return response()->json(
            DB::table("audit_logs")->select("module")->distinct()->orderBy("module")->pluck("module")
        );
    }

    public function roles()
    {
        return response()->json(
            DB::table("roles")->select("id", "name")->orderBy("name")->get()
        );
    }

    public function export(Request $request)
    {
        $request->validate([
            "format"          => "required|in:excel,pdf,csv",
            "retention_label" => "nullable|string",
        ]);

        $query = $this->baseQuery();
        if ($request->filled("date_from")) $query->whereDate("a.performed_at", ">=", $request->date_from);
        if ($request->filled("date_to"))   $query->whereDate("a.performed_at", "<=", $request->date_to);
        if ($request->filled("module"))    $query->where("a.module", $request->module);
        if ($request->filled("action"))    $query->whereIn("a.action", explode(",", $request->action));

        $logs     = $query->orderBy("a.performed_at", "desc")->get();
        $format   = strtolower($request->format);
        $label    = $request->get("retention_label", "Official Copy");
        $filename = "audit_logs_" . now()->format("Ymd_His");
        $includeIp = $request->filled("include_ip");

        if ($format === "csv") {
            return $this->exportCsv($logs, $filename, $includeIp);
        }

        if ($format === "excel") {
            return $this->exportExcel($logs, $filename, $label, $includeIp);
        }

        if ($format === "pdf") {
            return $this->exportPdf($logs, $filename, $label, $includeIp);
        }
    }

    private function exportCsv($logs, $filename, $includeIp)
    {
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}.csv",
        ];
        $callback = function () use ($logs, $includeIp) {
            $file    = fopen("php://output", "w");
            $columns = ["Date/Time", "User", "Role", "Module", "Action", "Record", "Remarks"];
            if ($includeIp) $columns[] = "IP Address";
            fputcsv($file, $columns);
            foreach ($logs as $log) {
                $row = [
                    $log->performed_at,
                    $log->user_name ?? "System",
                    $log->role_name ?? "N/A",
                    $log->module,
                    strtoupper($log->action),
                    $log->record_code ?? "-",
                    $log->remarks ?? "-",
                ];
                if ($includeIp) $row[] = $log->ip_address ?? "-";
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    private function exportExcel($logs, $filename, $label, $includeIp)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Audit Logs");

        // Title
        $sheet->mergeCells("A1:G1");
        $sheet->setCellValue("A1", "LGU Tuao - Audit Logs ({$label})");
        $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB("1a2a4a");
        $sheet->getStyle("A1")->getFont()->getColor()->setRGB("FFFFFF");

        $sheet->mergeCells("A2:G2");
        $sheet->setCellValue("A2", "Generated: " . now()->format("F d, Y h:i A"));
        $sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Headers
        $cols    = ["A" => "Date/Time", "B" => "User", "C" => "Role", "D" => "Module", "E" => "Action", "F" => "Record", "G" => "Remarks"];
        if ($includeIp) $cols["H"] = "IP Address";

        $row = 4;
        foreach ($cols as $col => $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
            $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("{$col}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB("c0392b");
            $sheet->getStyle("{$col}{$row}")->getFont()->getColor()->setRGB("FFFFFF");
        }

        // Data
        $row = 5;
        foreach ($logs as $log) {
            $sheet->setCellValue("A{$row}", $log->performed_at);
            $sheet->setCellValue("B{$row}", $log->user_name ?? "System");
            $sheet->setCellValue("C{$row}", $log->role_name ?? "N/A");
            $sheet->setCellValue("D{$row}", $log->module);
            $sheet->setCellValue("E{$row}", strtoupper($log->action));
            $sheet->setCellValue("F{$row}", $log->record_code ?? "-");
            $sheet->setCellValue("G{$row}", $log->remarks ?? "-");
            if ($includeIp) $sheet->setCellValue("H{$row}", $log->ip_address ?? "-");

            if ($row % 2 === 0) {
                $lastCol = $includeIp ? "H" : "G";
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB("f8f9fa");
            }
            $row++;
        }

        // Auto width
        foreach (array_keys($cols) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $temp   = tempnam(sys_get_temp_dir(), "audit_");
        $writer->save($temp);

        return response()->download($temp, "{$filename}.xlsx", [
            "Content-Type" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        ])->deleteFileAfterSend(true);
    }

    private function exportPdf($logs, $filename, $label, $includeIp)
    {
        $html = view("exports.audit-logs", compact("logs", "label", "includeIp", "filename"))->render();
        $pdf  = Pdf::loadHTML($html)->setPaper("a4", "landscape");
        return $pdf->download("{$filename}.pdf");
    }

    private function baseQuery()
    {
        return DB::table("audit_logs as a")
            ->leftJoin("users as u", "a.user_id", "=", "u.id")
            ->leftJoin("roles as r", "u.role_id", "=", "r.id")
            ->select(
                "a.id", "a.action", "a.module", "a.record_id",
                "a.record_code", "a.old_values", "a.new_values",
                "a.ip_address", "a.remarks", "a.performed_at",
                "u.name as user_name", "u.position",
                "r.name as role_name"
            );
    }

    private function getStats()
    {
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $totalToday     = AuditLog::whereDate("performed_at", $today)->count();
        $totalYesterday = AuditLog::whereDate("performed_at", $yesterday)->count();
        $percentChange  = $totalYesterday > 0
            ? round((($totalToday - $totalYesterday) / $totalYesterday) * 100)
            : 0;

        return [
            "total_events_24h"   => $totalToday,
            "percent_change"     => $percentChange,
            "security_alerts"    => AuditLog::whereIn("action", ["deleted", "login_failed", "unauthorized"])->whereDate("performed_at", $today)->count(),
            "modules_active"     => DB::table("audit_logs")->whereDate("performed_at", $today)->distinct("module")->count("module"),
            "active_admin_users" => DB::table("users")->where("is_active", 1)->count(),
        ];
    }
}
