<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAccomplishment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'target_date' => 'date',
        'report_period' => 'date',
        'completion_date' => 'date',
        'percent_complete' => 'decimal:2',
        'expected_percent' => 'decimal:2',
        'variance_percent' => 'decimal:2',
        'elapsed_days' => 'integer',
        'duration_days' => 'integer',
        'validated_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function documents()
    {
        return $this->hasMany(AccomplishmentDocument::class);
    }
}
