<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditLogger;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $payment = DB::transaction(function () use ($request, $validated) {
            $invoice = Invoice::query()
                ->whereKey($validated['invoice_id'])
                ->lockForUpdate()
                ->withSum('payments', 'amount_paid')
                ->firstOrFail();

            $paidAmount = (float) ($invoice->payments_sum_amount_paid ?? 0);
            $invoiceAmount = (float) $invoice->invoice_amount;
            $remainingBalance = max(0, $invoiceAmount - $paidAmount);
            $paymentAmount = (float) $validated['amount'];
            $invoiceOldValues = $invoice->toArray();

            if ($remainingBalance <= 0) {
                throw new HttpResponseException(response()->json([
                    'message' => 'This invoice is already fully paid.',
                ], 422));
            }

            if ($paymentAmount > $remainingBalance) {
                throw new HttpResponseException(response()->json([
                    'message' => 'Payment exceeds the remaining balance of ' . number_format($remainingBalance, 2, '.', ''),
                ], 422));
            }

            $payment = Payment::create([
                'invoice_id' => $validated['invoice_id'],
                'amount_paid' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'recorded_by' => $request->user()->id,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            AuditLogger::record(
                $request,
                'created',
                'payments',
                $payment->id,
                "Payment for Invoice #{$validated['invoice_id']}",
                null,
                $payment->toArray()
            );

            if ($paidAmount + $paymentAmount >= $invoiceAmount && $invoice->status !== 'Paid') {
                $invoice->update(['status' => 'Paid']);

                AuditLogger::record(
                    $request,
                    'updated',
                    'invoices',
                    $invoice->id,
                    $invoice->invoice_number,
                    $invoiceOldValues,
                    $invoice->fresh()->toArray()
                );
            }

            return $payment;
        });

        return response()->json([
            'message' => 'Payment disbursed successfully.',
            'data' => $this->formatPayment($payment),
        ], 201);
    }

    private function formatPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'amount_paid' => (float) $payment->amount_paid,
            'payment_date' => optional($payment->payment_date)->format('Y-m-d'),
            'payment_method' => $payment->payment_method,
            'recorded_by' => $payment->recorder?->name,
            'remarks' => $payment->remarks,
            'created_at' => optional($payment->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
