<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'status',
        'discount_type',
        'discount_amount',
        'tax_amount',
        'shipping_charges',
        'other_charges',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_charges' => 'decimal:2',
        'other_charges' => 'decimal:2',
    ];

    public const STATUS_ORDERED = 'ordered';
    public const STATUS_PENDING = 'pending';
    public const STATUS_RECEIVED = 'received';

    public const STATUSES = [
        self::STATUS_ORDERED,
        self::STATUS_PENDING,
        self::STATUS_RECEIVED,
    ];

    public const PAYMENT_STATUS_DUE = 'due';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_PAID = 'paid';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_DUE,
        self::PAYMENT_STATUS_PARTIAL,
        self::PAYMENT_STATUS_PAID,
    ];

    public const DISCOUNT_TYPE_NONE = 'none';
    public const DISCOUNT_TYPE_FIXED = 'fixed';
    public const DISCOUNT_TYPE_PERCENTAGE = 'percentage';

    public const DISCOUNT_TYPES = [
        self::DISCOUNT_TYPE_NONE,
        self::DISCOUNT_TYPE_FIXED,
        self::DISCOUNT_TYPE_PERCENTAGE,
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ORDERED => 'Ordered',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RECEIVED => 'Received',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ORDERED => 'blue',
            self::STATUS_PENDING => 'yellow',
            self::STATUS_RECEIVED => 'green',
            default => 'gray',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_STATUS_DUE => 'Due',
            self::PAYMENT_STATUS_PARTIAL => 'Partial',
            self::PAYMENT_STATUS_PAID => 'Paid',
            default => ucfirst($this->payment_status),
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_STATUS_DUE => 'red',
            self::PAYMENT_STATUS_PARTIAL => 'yellow',
            self::PAYMENT_STATUS_PAID => 'green',
            default => 'gray',
        };
    }
}
