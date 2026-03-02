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
        'invoice_no',
        'supplier_id',
        'payment_method_id',
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
        'notes',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (empty($purchase->invoice_no)) {
                $purchase->invoice_no = self::generateInvoiceNo();
            }
        });
    }

    public static function generateInvoiceNo(): string
    {
        $prefix = 'PO-';
        $lastPurchase = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastPurchase ? ((int) str_replace($prefix, '', $lastPurchase->invoice_no)) + 1 : 1;
        
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    protected $casts = [
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
        'due_amount' => 'integer',
        'discount_amount' => 'integer',
        'tax_amount' => 'integer',
        'shipping_charges' => 'integer',
        'other_charges' => 'integer',
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

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
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
