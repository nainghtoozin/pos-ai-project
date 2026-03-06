<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE purchases MODIFY COLUMN paid_amount DECIMAL(15,4) DEFAULT 0");
        DB::statement("ALTER TABLE purchases MODIFY COLUMN due_amount DECIMAL(15,4) DEFAULT 0");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE purchases MODIFY COLUMN paid_amount DECIMAL(15,4) NOT NULL");
        DB::statement("ALTER TABLE purchases MODIFY COLUMN due_amount DECIMAL(15,4) NOT NULL");
    }
};
