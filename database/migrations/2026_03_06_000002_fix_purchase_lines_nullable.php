<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE purchase_lines MODIFY COLUMN discount_amount INT UNSIGNED DEFAULT 0");
        DB::statement("ALTER TABLE purchase_lines MODIFY COLUMN selling_price INT UNSIGNED DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE purchase_lines MODIFY COLUMN discount_amount INT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE purchase_lines MODIFY COLUMN selling_price INT UNSIGNED NOT NULL");
    }
};
