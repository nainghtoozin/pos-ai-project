<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_number',
        'account_name',
        'type',
        'is_active',
        'is_system',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            if ($model->is_system) {
                throw new \Exception('Cannot modify system payment method.');
            }
        });

        static::deleting(function ($model) {
            if ($model->is_system) {
                throw new \Exception('Cannot delete system payment method.');
            }
        });
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystemDefault($query)
    {
        return $query->where('is_default', true);
    }
}
