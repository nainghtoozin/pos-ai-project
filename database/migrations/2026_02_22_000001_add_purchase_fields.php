<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->decimal('purchase_price', 15, 2)->after('quantity')->nullable();
            $table->decimal('selling_price', 15, 2)->after('purchase_price')->nullable();
            $table->decimal('discount_amount', 15, 2)->after('selling_price')->default(0);
            $table->decimal('line_total', 15, 2)->after('discount_amount')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'selling_price', 'discount_amount', 'line_total']);
        });
    }
};
