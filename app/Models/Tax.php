<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'percentage',
        'is_active',
    ];

    protected $casts = [
        'percentage' => 'integer',
        'is_active' => 'boolean',
    ];
}
