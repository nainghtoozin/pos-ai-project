<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $cashExists = PaymentMethod::where('name', 'Cash')->exists();

        if (!$cashExists) {
            PaymentMethod::create([
                'name' => 'Cash',
                'type' => 'cash',
                'is_active' => true,
                'is_system' => true,
                'is_default' => true,
            ]);
        }
    }
}
