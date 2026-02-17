<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseLine;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FIFOInventoryService
{
    public function processSale(array $items, ?int $customerId = null, ?string $notes = null): Sale
    {
        return DB::transaction(function () use ($items, $customerId, $notes) {
            $totalAmount = 0;
            $totalCost = 0;
            $totalProfit = 0;
            $saleItemsData = [];

            foreach ($items as $item) {
                $product = Product::with('stock')->findOrFail($item['product_id']);
                $quantity = $item['quantity'];
                $salePrice = $item['sale_price'];

                $productStock = ProductStock::lockForUpdate()
                    ->where('product_id', $product->id)
                    ->first();

                if (!$productStock || $productStock->current_stock < $quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $fifoResult = $this->deductFIFO($product->id, $quantity);

                $lineCost = $fifoResult['total_cost'];
                $lineProfit = ($salePrice * $quantity) - $lineCost;

                $totalAmount += $salePrice * $quantity;
                $totalCost += $lineCost;
                $totalProfit += $lineProfit;

                $saleItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'sale_price' => $salePrice,
                    'cost_price' => $lineCost,
                    'profit' => $lineProfit,
                ];
            }

            $sale = Sale::create([
                'customer_id' => $customerId,
                'sale_date' => now()->toDateString(),
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'paid_amount' => $totalAmount,
                'due_amount' => 0,
                'status' => 'completed',
                'notes' => $notes,
            ]);

            foreach ($saleItemsData as $itemData) {
                $sale->items()->create($itemData);
            }

            return $sale;
        });
    }

    private function deductFIFO(int $productId, float $quantity): array
    {
        $remainingToDeduct = $quantity;
        $totalCost = 0;

        $purchaseLines = PurchaseLine::where('product_id', $productId)
            ->where('remaining_qty', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($purchaseLines as $line) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $deductFromLine = min($line->remaining_qty, $remainingToDeduct);
            $lineCost = $deductFromLine * $line->cost_price;

            $line->decrement('remaining_qty', $deductFromLine);

            $remainingToDeduct -= $deductFromLine;
            $totalCost += $lineCost;
        }

        if ($remainingToDeduct > 0) {
            throw new \Exception("FIFO calculation error: insufficient purchase lines for product ID {$productId}");
        }

        ProductStock::where('product_id', $productId)
            ->decrement('current_stock', $quantity);

        return [
            'total_cost' => $totalCost,
        ];
    }

    public function addPurchase(array $lines, ?int $supplierId = null, ?string $purchaseDate = null, ?string $notes = null)
    {
        return DB::transaction(function () use ($lines, $supplierId, $purchaseDate, $notes) {
            $totalAmount = 0;

            foreach ($lines as $line) {
                $totalAmount += $line['quantity'] * $line['cost_price'];
            }

            $purchase = \App\Models\Purchase::create([
                'supplier_id' => $supplierId,
                'purchase_date' => $purchaseDate ?? now()->toDateString(),
                'total_amount' => $totalAmount,
                'notes' => $notes,
                'status' => 'received',
            ]);

            foreach ($lines as $line) {
                $purchaseLine = $purchase->lines()->create([
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'cost_price' => $line['cost_price'],
                    'remaining_qty' => $line['quantity'],
                ]);

                $productStock = ProductStock::updateOrCreate(
                    ['product_id' => $line['product_id']],
                    []
                );

                ProductStock::where('product_id', $line['product_id'])
                    ->increment('current_stock', $line['quantity']);
            }

            return $purchase;
        });
    }

    public function getCurrentStock(int $productId): float
    {
        $stock = ProductStock::where('product_id', $productId)->first();
        return $stock?->current_stock ?? 0;
    }

    public function getFIFOCost(int $productId, float $quantity): array
    {
        $remainingToCalculate = $quantity;
        $totalCost = 0;
        $breakdown = [];

        $purchaseLines = PurchaseLine::where('product_id', $productId)
            ->where('remaining_qty', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($purchaseLines as $line) {
            if ($remainingToCalculate <= 0) {
                break;
            }

            $useFromLine = min($line->remaining_qty, $remainingToCalculate);
            $cost = $useFromLine * $line->cost_price;

            $breakdown[] = [
                'purchase_line_id' => $line->id,
                'quantity' => $useFromLine,
                'cost_price' => $line->cost_price,
                'line_cost' => $cost,
            ];

            $totalCost += $cost;
            $remainingToCalculate -= $useFromLine;
        }

        return [
            'total_cost' => $totalCost,
            'breakdown' => $breakdown,
        ];
    }
}
