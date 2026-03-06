<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_lines', 'source_type')) {
                $table->string('source_type', 50)->default('purchase')->after('product_id');
            }

            if (!Schema::hasColumn('purchase_lines', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
        });

        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
        });

        DB::statement('ALTER TABLE purchase_lines MODIFY purchase_id BIGINT UNSIGNED NULL');

        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->foreign('purchase_id')->references('id')->on('purchases')->cascadeOnDelete();
            $table->index(['source_type', 'source_id']);
        });

        DB::statement("UPDATE purchase_lines SET source_type = 'purchase' WHERE source_type IS NULL");
        DB::statement('UPDATE purchase_lines SET source_id = purchase_id WHERE source_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
            if (Schema::hasColumn('purchase_lines', 'source_type')) {
                $table->dropIndex('purchase_lines_source_type_source_id_index');
                $table->dropColumn(['source_type', 'source_id']);
            }
        });

        DB::statement('ALTER TABLE purchase_lines MODIFY purchase_id BIGINT UNSIGNED NOT NULL');

        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->foreign('purchase_id')->references('id')->on('purchases')->cascadeOnDelete();
        });
    }
};
