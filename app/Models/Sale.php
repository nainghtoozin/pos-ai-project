<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'branch_id',
        'customer_id',
        'user_id',
        'subtotal',
        'discount',
        'tax',
        'shipping',
        'grand_total',
        'total_cost',
        'total_profit',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'status',
        'suspended_at',
        'note',
        'sale_date',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'suspended_at' => 'datetime',
        'subtotal' => 'integer',
        'discount' => 'integer',
        'tax' => 'integer',
        'shipping' => 'integer',
        'grand_total' => 'integer',
        'total_cost' => 'integer',
        'total_profit' => 'integer',
        'paid_amount' => 'integer',
        'due_amount' => 'integer',
    ];

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_DUE = 'due';

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public static function generateInvoiceNo(): string
    {
        $prefix = 'INV-';
        $lastSale = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastSale ? (intval(substr($lastSale->invoice_no, -6)) + 1) : 1;
        return $prefix . date('Ymd') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function shouldDeductStock(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED]) && 
               in_array($this->payment_status, [self::PAYMENT_PAID, self::PAYMENT_PARTIAL]);
    }
}
