<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    /**
     * Keep module audit records consistent without coupling models to HTTP.
     */
    public static function record(
        Request $request,
        string $action,
        string $module,
        ?int $recordId = null,
        ?string $recordCode = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $remarks = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'record_code' => $recordCode,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'remarks' => $remarks,
            'performed_at' => now(),
        ]);
    }
}
