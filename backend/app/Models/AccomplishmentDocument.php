<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccomplishmentDocument extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function accomplishment()
    {
        return $this->belongsTo(ProjectAccomplishment::class, 'project_accomplishment_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
