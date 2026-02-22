<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'remaining_qty']);
        });
    }

    public function down(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->decimal('cost_price', 15, 4)->after('quantity');
            $table->decimal('remaining_qty', 15, 4)->after('cost_price');
        });
    }
};
