<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function ($table) {
            $table->boolean('is_system')->default(false)->after('is_active');
            $table->boolean('is_default')->default(false)->after('is_system');
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function ($table) {
            $table->dropColumn(['is_system', 'is_default']);
        });
    }
};
