<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'barcode',
        'sku',
        'product_type',
        'category_id',
        'brand_id',
        'unit_id',
        'tax_id',
        'description',
        'image',
        'is_active',
        'sale_price',
        'purchase_price',
        'wholesale_price',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sale_price' => 'decimal:4',
        'purchase_price' => 'decimal:4',
        'wholesale_price' => 'decimal:4',
    ];

    protected $appends = [
        'image_url',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function purchaseLines(): HasMany
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(ProductStock::class, 'product_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function latestPurchase(): HasOne
    {
        return $this->hasOne(PurchaseLine::class)->latestOfMany();
    }

    public function scopeSingle($query)
    {
        return $query->where('product_type', 'single');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }
}
