<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function ($table) {
            if (!Schema::hasColumn('sales', 'shipping')) {
                $table->unsignedInteger('shipping')->default(0)->after('tax');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function ($table) {
            $table->dropColumn('shipping');
        });
    }
};
