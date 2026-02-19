<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->enum('type', [
                'opening',
                'purchase',
                'sale',
                'sale_return',
                'purchase_return',
                'transfer_in',
                'transfer_out',
                'adjustment_in',
                'adjustment_out'
            ]);
            $table->decimal('quantity', 15, 4);
            $table->string('reference_no')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index('product_id');
            $table->index('type');
            $table->index('created_at');
            $table->index('reference_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
