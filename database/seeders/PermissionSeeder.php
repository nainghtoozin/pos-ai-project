<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Role Management
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            // Unit Management
            'unit.view',
            'unit.create',
            'unit.edit',
            'unit.delete',

            // Brand Management
            'brand.view',
            'brand.create',
            'brand.edit',
            'brand.delete',

            // Category Management
            'category.view',
            'category.create',
            'category.edit',
            'category.delete',

            // Product Management
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',

            // Purchase
            'purchase.view',
            'purchase.create',
            'purchase.edit',
            'purchase.delete',

            // Sell
            'sell.view',
            'sell.create',
            'sell.edit',
            'sell.delete',

            // Stock
            'stock.adjust',
            'stock.transfer',
            'stock.view',

            //tax
            'tax.view',
            'tax.create',
            'tax.edit',
            'tax.delete',

            // Report
            'report.view',

            // System
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
