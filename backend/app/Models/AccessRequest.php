<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function requestedRole()
    {
        return $this->belongsTo(Role::class, 'requested_role_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
