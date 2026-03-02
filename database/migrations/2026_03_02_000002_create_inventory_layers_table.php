<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('source_type', 50)->nullable(); // 'purchase', 'adjustment'
            $table->unsignedBigInteger('source_id')->nullable(); // purchase_id or adjustment_id
            $table->integer('quantity')->unsigned(); // Original quantity received
            $table->integer('remaining_quantity')->unsigned(); // Quantity still available
            $table->integer('unit_cost')->unsigned(); // Cost per unit
            $table->timestamps();

            $table->index(['product_id', 'remaining_quantity']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_layers');
    }
};
