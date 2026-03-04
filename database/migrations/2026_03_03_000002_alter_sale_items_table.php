<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function ($table) {
            if (!Schema::hasColumn('sale_items', 'unit_price')) {
                $table->unsignedInteger('unit_price')->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('sale_items', 'total')) {
                $table->unsignedInteger('total')->default(0)->after('cost_price');
            }
        });
    }

    public function down(): void
    {
    }
};
