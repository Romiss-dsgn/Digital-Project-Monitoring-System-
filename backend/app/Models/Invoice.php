<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'invoice_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if ($invoice->status === null) {
                $invoice->status = 'Pending';
            }
        });
    }

    protected static function booted(): void
    {
        static::saved(function (Invoice $invoice) {
            if (! $invoice->wasRecentlyCreated && ! $invoice->wasChanged(['status', 'cashflow_period_id'])) {
                return;
            }

            $invoice->syncCashflowPeriods();
        });

        static::deleted(function (Invoice $invoice) {
            $invoice->syncCashflowPeriods($invoice->getOriginal('cashflow_period_id'));
        });
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function cashflowPeriod()
    {
        return $this->belongsTo(CashflowPeriod::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->hasMany(InvoiceDocument::class);
    }

    public function syncCashflowPeriods(?int $originalPeriodId = null): void
    {
        collect([$this->cashflow_period_id, $originalPeriodId])
            ->filter()
            ->unique()
            ->each(function (int $periodId) {
                CashflowPeriod::find($periodId)?->syncFinancials();
            });
    }
}
