<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->date('adjustment_date');
            $table->enum('type', ['increase', 'decrease']);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('adjustment_date');
            $table->index('type');
            $table->index('reference_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
