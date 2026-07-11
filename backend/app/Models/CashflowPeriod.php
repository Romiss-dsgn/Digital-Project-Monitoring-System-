<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashflowPeriod extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'planned_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance' => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function syncFinancials(): void
    {
        $actualAmount = (float) Payment::query()
            ->whereHas('invoice', fn ($query) => $query->where('cashflow_period_id', $this->id))
            ->sum('amount_paid');

        $plannedAmount = (float) $this->planned_amount;

        $this->forceFill([
            'actual_amount' => $actualAmount,
            'variance' => $plannedAmount - $actualAmount,
        ])->saveQuietly();
    }
}
