<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLayer extends Model
{
    protected $fillable = [
        'product_id',
        'source_type',
        'source_id',
        'quantity',
        'remaining_quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'unit_cost' => 'integer',
    ];

    public const SOURCE_PURCHASE = 'purchase';
    public const SOURCE_ADJUSTMENT = 'adjustment';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalValue(): int
    {
        return $this->remaining_quantity * $this->unit_cost;
    }
}
