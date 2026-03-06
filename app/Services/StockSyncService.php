<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseLine;
use Illuminate\Support\Facades\DB;

class StockSyncService
{
    public function syncProductStock(int $productId): int
    {
        $product = Product::findOrFail($productId);
        
        $totalStock = PurchaseLine::getCurrentStock($productId);
        
        $product->stock = $totalStock;
        $product->save();
        
        ProductStock::updateOrCreate(
            ['product_id' => $productId],
            ['current_stock' => $totalStock]
        );
        
        return $totalStock;
    }

    public function calculateCurrentStock(int $productId): int
    {
        return PurchaseLine::getCurrentStock($productId);
    }

    public function syncAllProducts(): array
    {
        $results = [
            'synced' => 0,
            'errors' => [],
        ];

        $products = Product::all();

        foreach ($products as $product) {
            try {
                $this->syncProductStock($product->id);
                $results['synced']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function rebuildStockFromPurchaseLines(): array
    {
        $results = [
            'synced' => 0,
            'errors' => [],
        ];

        $products = Product::all();

        foreach ($products as $product) {
            try {
                $totalStock = PurchaseLine::getCurrentStock($product->id);

                $product->stock = $totalStock;
                $product->save();

                ProductStock::updateOrCreate(
                    ['product_id' => $product->id],
                    ['current_stock' => $totalStock]
                );

                $results['synced']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function incrementStock(int $productId, int $quantity): int
    {
        return PurchaseLine::getCurrentStock($productId) + $quantity;
    }

    public function decrementStock(int $productId, int $quantity): int
    {
        $currentStock = PurchaseLine::getCurrentStock($productId);
        
        if ($currentStock < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$currentStock}, Requested: {$quantity}");
        }
        
        return $currentStock - $quantity;
    }
}
