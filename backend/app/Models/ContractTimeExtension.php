<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTimeExtension extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'original_end_date' => 'date',
        'requested_new_end_date' => 'date',
        'approved_new_end_date' => 'date',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
