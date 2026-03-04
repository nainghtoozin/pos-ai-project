<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'name',
        'mobile',
        'email',
        'phone',
        'address',
        'note',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public static function generateContactId(): string
    {
        $prefix = 'CUST-';
        $lastCustomer = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastCustomer ? (intval(substr($lastCustomer->contact_id, -5)) + 1) : 1;
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
