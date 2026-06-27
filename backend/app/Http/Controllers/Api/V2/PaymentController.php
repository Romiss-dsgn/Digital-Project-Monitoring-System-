<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditLogger;
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
            $payment = Payment::create([
                'invoice_id' => $validated['invoice_id'],
                'amount_paid' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'recorded_by' => $request->user()->id,
                'remarks' => $validated['remarks'],
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