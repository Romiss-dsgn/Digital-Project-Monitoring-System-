<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorPerformanceRating extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'evaluation_period_start' => 'date',
        'evaluation_period_end' => 'date',
        'completion_rate' => 'decimal:2',
        'timeline_compliance' => 'decimal:2',
        'accomplishment_score' => 'decimal:2',
        'overall_rating' => 'decimal:2',
        'evaluated_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
