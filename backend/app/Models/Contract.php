<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'original_contract_amount' => 'decimal:2',
        'revised_contract_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'notice_to_proceed_date' => 'date',
        'signed_date' => 'date',
        'approved_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents()
    {
        return $this->hasMany(ContractDocument::class);
    }

    public function cashflowPeriods()
    {
        return $this->hasMany(CashflowPeriod::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function variationOrders()
    {
        return $this->hasMany(VariationOrder::class);
    }

    public function timeExtensions()
    {
        return $this->hasMany(ContractTimeExtension::class);
    }

    public function workSuspensions()
    {
        return $this->hasMany(WorkSuspension::class);
    }

    public function performanceRatings()
    {
        return $this->hasMany(ContractorPerformanceRating::class);
    }
}
