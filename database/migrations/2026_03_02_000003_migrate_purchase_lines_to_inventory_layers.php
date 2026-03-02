<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $purchaseLines = DB::table('purchase_lines')
            ->where('remaining_qty', '>', 0)
            ->get();

        foreach ($purchaseLines as $line) {
            DB::table('inventory_layers')->insert([
                'product_id' => $line->product_id,
                'source_type' => 'purchase',
                'source_id' => $line->purchase_id,
                'quantity' => $line->quantity,
                'remaining_quantity' => $line->remaining_qty,
                'unit_cost' => $line->purchase_price,
                'created_at' => $line->created_at,
                'updated_at' => $line->updated_at,
            ]);
        }

        DB::table('products')
            ->update(['stock' => DB::raw('(
                SELECT COALESCE(SUM(remaining_qty), 0)
                FROM purchase_lines
                WHERE purchase_lines.product_id = products.id
            )')]);
    }

    public function down(): void
    {
        DB::table('inventory_layers')->delete();
    }
};
