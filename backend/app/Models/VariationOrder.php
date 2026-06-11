<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount_change' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents()
    {
        return $this->hasMany(VariationOrderDocument::class);
    }
}
