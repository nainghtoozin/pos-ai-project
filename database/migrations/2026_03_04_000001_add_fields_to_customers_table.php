<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function ($table) {
            if (!Schema::hasColumn('customers', 'contact_id')) {
                $table->string('contact_id')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('customers', 'mobile')) {
                $table->string('mobile')->nullable()->after('contact_id');
            }
            if (!Schema::hasColumn('customers', 'note')) {
                $table->text('note')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function ($table) {
            $table->dropColumn(['contact_id', 'mobile', 'note']);
        });
    }
};
