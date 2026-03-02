<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $fillable = [
        'reference_no',
        'adjustment_date',
        'type',
        'note',
        'created_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
    ];

    public const TYPE_INCREASE = 'increase';
    public const TYPE_DECREASE = 'decrease';

    public const TYPES = [
        self::TYPE_INCREASE,
        self::TYPE_DECREASE,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateReferenceNo(): string
    {
        $prefix = 'SA-';
        $lastAdjustment = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastAdjustment ? (intval(substr($lastAdjustment->reference_no, -5)) + 1) : 1;
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
