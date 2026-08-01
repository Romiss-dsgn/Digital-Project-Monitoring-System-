<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationOrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'line_number' => 'integer',
        'original_qty' => 'decimal:3',
        'original_unit_cost' => 'decimal:2',
        'original_total_cost' => 'decimal:2',
        'additive_qty' => 'decimal:3',
        'additive_unit_cost' => 'decimal:2',
        'additive_total_cost' => 'decimal:2',
        'deductive_qty' => 'decimal:3',
        'deductive_unit_cost' => 'decimal:2',
        'deductive_total_cost' => 'decimal:2',
        'net_line_cost' => 'decimal:2',
    ];

    public function variationOrder()
    {
        return $this->belongsTo(VariationOrder::class);
    }
}
