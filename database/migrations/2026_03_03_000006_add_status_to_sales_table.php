<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function ($table) {
            if (!Schema::hasColumn('sales', 'status')) {
                $table->enum('status', ['completed', 'draft', 'suspended', 'cancelled'])->default('completed')->after('payment_status');
            }
            if (!Schema::hasColumn('sales', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function ($table) {
            $table->dropColumn(['status', 'suspended_at']);
        });
    }
};
