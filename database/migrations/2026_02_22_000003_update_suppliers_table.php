<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('contact_id', 20)->unique()->nullable()->after('id');
            $table->string('mobile')->nullable()->after('name');
            $table->string('social_profile')->nullable()->after('address');
            $table->decimal('opening_balance', 15, 2)->default(0)->after('social_profile');
            $table->decimal('advance_balance', 15, 2)->default(0)->after('opening_balance');
            $table->string('township')->nullable()->after('advance_balance');
            $table->string('city')->nullable()->after('township');
            $table->unsignedBigInteger('created_by')->nullable()->after('city');
            
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['email', 'phone', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('address');
            
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'contact_id',
                'mobile',
                'social_profile',
                'opening_balance',
                'advance_balance',
                'township',
                'city',
                'created_by',
            ]);
        });
    }
};
