<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'quantity',
        'unit_cost',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'integer',
    ];

    public const REASON_DAMAGE = 'damage';
    public const REASON_EXPIRED = 'expired';
    public const REASON_LOST = 'lost';
    public const REASON_FOUND = 'found';
    public const REASON_CORRECTION = 'correction';
    public const REASON_OTHER = 'other';

    public const REASONS = [
        self::REASON_DAMAGE,
        self::REASON_EXPIRED,
        self::REASON_LOST,
        self::REASON_FOUND,
        self::REASON_CORRECTION,
        self::REASON_OTHER,
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getLineTotal(): int
    {
        return $this->quantity * ($this->unit_cost ?? 0);
    }
}
