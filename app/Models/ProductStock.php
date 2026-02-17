<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_id',
        'current_stock',
    ];

    protected $casts = [
        'current_stock' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
