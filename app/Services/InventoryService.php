<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseLine;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function handleAdjustment(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $adjustment = StockAdjustment::create([
                'reference_no' => StockAdjustment::generateReferenceNo(),
                'adjustment_date' => $data['adjustment_date'],
                'type' => $data['type'],
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $itemData) {
                $adjustmentItem = StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'] ?? null,
                    'reason' => $itemData['reason'],
                ]);

                if ($data['type'] === StockAdjustment::TYPE_DECREASE) {
                    $this->deductFromPurchaseLines($adjustmentItem);
                } else {
                    $this->addToPurchaseLines($adjustmentItem);
                }
            }

            return $adjustment;
        });
    }

    public function addToPurchaseLines(StockAdjustmentItem $item): void
    {
        $product = $item->product;
        $unitCost = $item->unit_cost;

        if (!$unitCost) {
            throw new \Exception("Unit cost is required for stock increase");
        }

        PurchaseLine::addStock(
            $product->id,
            $item->quantity,
            $unitCost,
            PurchaseLine::SOURCE_ADJUSTMENT,
            $item->id,
            null
        );

        $this->syncProductStock($product);
    }

    public function deductFromPurchaseLines(StockAdjustmentItem $item): void
    {
        $product = $item->product;
        $quantityToDeduct = $item->quantity;
        $reason = $item->reason;

        $totalAvailable = PurchaseLine::getCurrentStock($product->id);

        if ($totalAvailable < $quantityToDeduct) {
            throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$totalAvailable}, Requested: {$quantityToDeduct}");
        }

        $remainingToDeduct = $quantityToDeduct;
        $layers = PurchaseLine::where('product_id', $product->id)
            ->where('remaining_qty', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($layers as $layer) {
            if ($remainingToDeduct <= 0) break;

            $deductFromThis = min($layer->remaining_qty, $remainingToDeduct);
            $layer->decrement('remaining_qty', $deductFromThis);
            $remainingToDeduct -= $deductFromThis;
        }

        $item->load('adjustment');

        StockMovement::create([
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_ADJUSTMENT_OUT,
            'quantity' => -$item->quantity,
            'reference_no' => $item->adjustment->reference_no,
            'created_by' => auth()->id(),
            'notes' => "Stock adjustment out - Reason: {$reason}",
        ]);

        $this->syncProductStock($product);
    }

    public function syncProductStock(Product $product): void
    {
        $totalStock = PurchaseLine::getCurrentStock($product->id);

        $product->stock = $totalStock;
        $product->save();

        ProductStock::updateOrCreate(
            ['product_id' => $product->id],
            ['current_stock' => $totalStock]
        );
    }

    public function getCurrentStock(int $productId): int
    {
        return PurchaseLine::getCurrentStock($productId);
    }

    public function getFIFOLayers(int $productId)
    {
        return PurchaseLine::getFIFOLayers($productId);
    }

    public function getAverageCost(int $productId): int
    {
        $layers = $this->getFIFOLayers($productId);
        
        if ($layers->isEmpty()) {
            return 0;
        }

        $totalQuantity = $layers->sum('remaining_qty');
        $totalCost = $layers->sum(fn($layer) => $layer->remaining_qty * $layer->purchase_price);

        return $totalQuantity > 0 ? intval($totalCost / $totalQuantity) : 0;
    }
}
