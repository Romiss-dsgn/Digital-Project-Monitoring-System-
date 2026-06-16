<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'approved_budget' => 'decimal:2',
        'progress_percent' => 'decimal:2',
        'target_start_date' => 'date',
        'target_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'is_archived' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function documents()
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function engineeringPlans()
    {
        return $this->hasMany(EngineeringPlan::class);
    }

    public function accomplishments()
    {
        return $this->hasMany(ProjectAccomplishment::class);
    }
}
