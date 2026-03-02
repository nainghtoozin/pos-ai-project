<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function ($table) {
            $table->bigInteger('quantity')->unsigned(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function ($table) {
            $table->bigInteger('quantity')->unsigned()->change();
        });
    }
};
