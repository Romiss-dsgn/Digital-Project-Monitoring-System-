<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngineeringPlan extends Model
{
    public const STATUS_FOR_REVIEW = 'for_review';
    public const STATUS_APPROVED   = 'approved';
    public const STATUS_REVISION   = 'revision';
    public const STATUS_UPLOADED   = 'uploaded';

    public const STATUSES = [
        self::STATUS_FOR_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_REVISION,
        self::STATUS_UPLOADED,
    ];

    public const PLAN_TYPES = [
        'Architectural',
        'Structural',
        'Electrical',
        'Mechanical',
        'Plumbing & Sanitary',
    ];

    protected $fillable = [
        'project_id',
        'plan_title',
        'plan_type',
        'version',
        'file_name',
        'file_path',
        'file_type',
        'status',
        'uploaded_by',
        'uploaded_at',
        'reviewed_by',
        'reviewed_at',
        'remarks',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
