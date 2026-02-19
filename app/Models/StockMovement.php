<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference_no',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public const TYPE_OPENING = 'opening';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_SALE = 'sale';
    public const TYPE_SALE_RETURN = 'sale_return';
    public const TYPE_PURCHASE_RETURN = 'purchase_return';
    public const TYPE_TRANSFER_IN = 'transfer_in';
    public const TYPE_TRANSFER_OUT = 'transfer_out';
    public const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    public const TYPE_ADJUSTMENT_OUT = 'adjustment_out';

    public const STOCK_IN = [
        self::TYPE_OPENING,
        self::TYPE_PURCHASE,
        self::TYPE_SALE_RETURN,
        self::TYPE_TRANSFER_IN,
        self::TYPE_ADJUSTMENT_IN,
    ];

    public const STOCK_OUT = [
        self::TYPE_SALE,
        self::TYPE_PURCHASE_RETURN,
        self::TYPE_TRANSFER_OUT,
        self::TYPE_ADJUSTMENT_OUT,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsStockInAttribute(): bool
    {
        return in_array($this->type, self::STOCK_IN);
    }

    public function getIsStockOutAttribute(): bool
    {
        return in_array($this->type, self::STOCK_OUT);
    }

    public function getSignedQuantityAttribute(): float
    {
        return $this->is_stock_in ? $this->quantity : -$this->quantity;
    }

    public static function getTypeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_OPENING => 'Opening Stock',
            self::TYPE_PURCHASE => 'Purchase',
            self::TYPE_SALE => 'Sale',
            self::TYPE_SALE_RETURN => 'Sale Return',
            self::TYPE_PURCHASE_RETURN => 'Purchase Return',
            self::TYPE_TRANSFER_IN => 'Transfer In',
            self::TYPE_TRANSFER_OUT => 'Transfer Out',
            self::TYPE_ADJUSTMENT_IN => 'Adjustment Increase',
            self::TYPE_ADJUSTMENT_OUT => 'Adjustment Decrease',
            default => ucfirst($type),
        };
    }

    public static function getTypeColor(string $type): string
    {
        return match ($type) {
            self::TYPE_OPENING, self::TYPE_PURCHASE, self::TYPE_SALE_RETURN, 
            self::TYPE_TRANSFER_IN, self::TYPE_ADJUSTMENT_IN => 'emerald',
            self::TYPE_SALE, self::TYPE_PURCHASE_RETURN, 
            self::TYPE_TRANSFER_OUT, self::TYPE_ADJUSTMENT_OUT => 'rose',
            default => 'gray',
        };
    }
}
