<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'name',
        'mobile',
        'address',
        'social_profile',
        'opening_balance',
        'advance_balance',
        'township',
        'city',
        'created_by',
    ];

    protected $casts = [
        'opening_balance' => 'integer',
        'advance_balance' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->contact_id)) {
                $supplier->contact_id = self::generateContactId();
            }
            if (empty($supplier->created_by)) {
                $supplier->created_by = auth()->id();
            }
        });
    }

    public static function generateContactId(): string
    {
        $lastSupplier = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastSupplier ? (intval(substr($lastSupplier->contact_id, -5)) + 1) : 1;
        return 'SUP-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
