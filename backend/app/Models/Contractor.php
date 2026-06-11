<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function performanceRatings()
    {
        return $this->hasMany(ContractorPerformanceRating::class);
    }
}
