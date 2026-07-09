<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saved(function (Payment $payment) {
            if (! $payment->wasRecentlyCreated && ! $payment->wasChanged(['invoice_id', 'amount_paid'])) {
                return;
            }

            $payment->syncCashflowPeriods($payment->getOriginal('invoice_id'));
        });

        static::deleted(function (Payment $payment) {
            $payment->syncCashflowPeriods($payment->getOriginal('invoice_id'));
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function syncCashflowPeriods(?int $originalInvoiceId = null): void
    {
        $invoiceIds = collect([$this->invoice_id, $originalInvoiceId])
            ->filter()
            ->unique();

        $periodIds = Invoice::query()
            ->whereIn('id', $invoiceIds)
            ->pluck('cashflow_period_id')
            ->filter()
            ->unique();

        $periodIds->each(function (int $periodId) {
            CashflowPeriod::find($periodId)?->syncFinancials();
        });
    }
}
