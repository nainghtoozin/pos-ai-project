<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->integer('remaining_qty')->after('quantity')->default(0);
            $table->index('remaining_qty');
        });

        // Initialize remaining_qty for existing records
        DB::statement('UPDATE purchase_lines SET remaining_qty = quantity');
    }

    public function down(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->dropColumn('remaining_qty');
        });
    }
};
