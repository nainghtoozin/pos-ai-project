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
        'customer_id',
        'sale_date',
        'total_amount',
        'total_cost',
        'total_profit',
        'paid_amount',
        'due_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'integer',
        'total_cost' => 'integer',
        'total_profit' => 'integer',
        'paid_amount' => 'integer',
        'due_amount' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
