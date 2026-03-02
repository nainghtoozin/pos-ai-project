<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'purchase_price',
        'selling_price',
        'discount_amount',
        'line_total',
        'remaining_qty',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'integer',
        'selling_price' => 'integer',
        'discount_amount' => 'integer',
        'line_total' => 'integer',
        'remaining_qty' => 'integer',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($line) {
            if (is_null($line->line_total)) {
                $line->line_total = ($line->quantity * $line->purchase_price) - ($line->discount_amount ?? 0);
            }
            if (is_null($line->remaining_qty)) {
                $line->remaining_qty = $line->quantity;
            }
        });
    }
}
