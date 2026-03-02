<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert decimal to integer for each table
        $tables = [
            'products' => ['purchase_price', 'sale_price', 'wholesale_price'],
            'purchases' => ['total_amount', 'paid_amount', 'due_amount', 'discount_amount', 'tax_amount', 'shipping_charges', 'other_charges'],
            'purchase_lines' => ['quantity', 'purchase_price', 'selling_price', 'discount_amount', 'line_total'],
            'purchase_payments' => ['amount'],
            'purchase_returns' => ['total_return_amount'],
            'purchase_return_items' => ['quantity', 'return_price', 'subtotal'],
            'sales' => ['total_amount', 'total_cost', 'total_profit', 'paid_amount', 'due_amount'],
            'sale_items' => ['quantity', 'sale_price', 'cost_price', 'profit'],
            'suppliers' => ['opening_balance', 'advance_balance'],
            'taxes' => ['percentage'],
            'product_stocks' => ['current_stock'],
            'stock_movements' => ['quantity'],
        ];

        foreach ($tables as $table => $columns) {
            foreach ($columns as $column) {
                try {
                    DB::statement("UPDATE {$table} SET {$column} = CAST({$column} AS UNSIGNED)");
                } catch (\Exception $e) {
                    // Skip if column doesn't exist or other error
                }
            }
        }

        // Now change column types
        Schema::table('products', function ($table) {
            $table->unsignedBigInteger('purchase_price')->change();
            $table->unsignedBigInteger('sale_price')->change();
            $table->unsignedBigInteger('wholesale_price')->change();
        });

        Schema::table('purchases', function ($table) {
            $table->unsignedBigInteger('total_amount')->change();
            $table->unsignedBigInteger('paid_amount')->change();
            $table->unsignedBigInteger('due_amount')->change();
            $table->unsignedBigInteger('discount_amount')->change();
            $table->unsignedBigInteger('tax_amount')->change();
            $table->unsignedBigInteger('shipping_charges')->change();
            $table->unsignedBigInteger('other_charges')->change();
        });

        Schema::table('purchase_lines', function ($table) {
            $table->unsignedBigInteger('quantity')->change();
            $table->unsignedBigInteger('purchase_price')->change();
            $table->unsignedBigInteger('selling_price')->change();
            $table->unsignedBigInteger('discount_amount')->change();
            $table->unsignedBigInteger('line_total')->change();
        });

        Schema::table('purchase_payments', function ($table) {
            $table->unsignedBigInteger('amount')->change();
        });

        Schema::table('purchase_returns', function ($table) {
            $table->unsignedBigInteger('total_return_amount')->change();
        });

        Schema::table('purchase_return_items', function ($table) {
            $table->unsignedBigInteger('quantity')->change();
            $table->unsignedBigInteger('return_price')->change();
            $table->unsignedBigInteger('subtotal')->change();
        });

        Schema::table('sales', function ($table) {
            $table->unsignedBigInteger('total_amount')->change();
            $table->unsignedBigInteger('total_cost')->change();
            $table->unsignedBigInteger('total_profit')->change();
            $table->unsignedBigInteger('paid_amount')->change();
            $table->unsignedBigInteger('due_amount')->change();
        });

        Schema::table('sale_items', function ($table) {
            $table->unsignedBigInteger('quantity')->change();
            $table->unsignedBigInteger('sale_price')->change();
            $table->unsignedBigInteger('cost_price')->change();
            $table->unsignedBigInteger('profit')->change();
        });

        Schema::table('suppliers', function ($table) {
            $table->unsignedBigInteger('opening_balance')->change();
            $table->unsignedBigInteger('advance_balance')->change();
        });

        Schema::table('taxes', function ($table) {
            $table->unsignedInteger('percentage')->change();
        });

        Schema::table('product_stocks', function ($table) {
            $table->unsignedBigInteger('current_stock')->change();
        });

        Schema::table('stock_movements', function ($table) {
            $table->unsignedBigInteger('quantity')->change();
        });
    }

    public function down(): void
    {
        // Not reversible
    }
};
