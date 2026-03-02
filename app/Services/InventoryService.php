<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockMovement;
use App\Models\InventoryLayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Handle stock adjustment - increase or decrease
     */
    public function handleAdjustment(array $data): StockAdjustment
    {
        Log::info('=== handleAdjustment START ===', $data);
        
        return DB::transaction(function () use ($data) {
            $adjustment = StockAdjustment::create([
                'reference_no' => StockAdjustment::generateReferenceNo(),
                'adjustment_date' => $data['adjustment_date'],
                'type' => $data['type'],
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            Log::info('=== Adjustment created ===', ['id' => $adjustment->id, 'ref' => $adjustment->reference_no]);

            foreach ($data['items'] as $itemData) {
                $adjustmentItem = StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'] ?? null,
                    'reason' => $itemData['reason'],
                ]);

                Log::info('=== Item created ===', ['item_id' => $adjustmentItem->id, 'adjustment_id' => $adjustmentItem->stock_adjustment_id]);

                if ($data['type'] === StockAdjustment::TYPE_DECREASE) {
                    $this->deductFromLayers($adjustmentItem);
                } else {
                    $this->createLayer($adjustmentItem);
                }
            }

            return $adjustment;
        });
    }

    /**
     * Create a new inventory layer for stock increase
     */
    public function createLayer(StockAdjustmentItem $item): void
    {
        $product = $item->product;
        $unitCost = $item->unit_cost;

        if (!$unitCost) {
            throw new \Exception("Unit cost is required for stock increase");
        }

        InventoryLayer::create([
            'product_id' => $product->id,
            'source_type' => InventoryLayer::SOURCE_ADJUSTMENT,
            'source_id' => $item->id,
            'quantity' => $item->quantity,
            'remaining_quantity' => $item->quantity,
            'unit_cost' => $unitCost,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'adjustment_in',
            'quantity' => $item->quantity,
            'reference_no' => $item->adjustment->reference_no,
            'created_by' => auth()->id(),
            'notes' => "Stock adjustment in - Reason: {$item->reason}, Cost: {$unitCost}",
        ]);

        $this->syncProductStock($product);
    }

    /**
     * Deduct stock using FIFO method from inventory_layers
     */
    public function deductFromLayers(StockAdjustmentItem $item): void
    {
        $product = $item->product;
        $quantityToDeduct = $item->quantity;
        $reason = $item->reason;

        Log::info('=== deductFromLayers START ===', [
            'product_id' => $product->id,
            'quantity' => $quantityToDeduct,
            'item_id' => $item->id
        ]);

        $layers = InventoryLayer::where('product_id', $product->id)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $totalAvailable = $layers->sum('remaining_quantity');
        
        Log::info('=== Layers found ===', [
            'count' => $layers->count(),
            'total' => $totalAvailable
        ]);

        if ($totalAvailable < $quantityToDeduct) {
            throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$totalAvailable}, Requested: {$quantityToDeduct}");
        }

        foreach ($layers as $layer) {
            if ($quantityToDeduct <= 0) break;

            $deductFromThis = min($layer->remaining_quantity, $quantityToDeduct);
            
            $layer->remaining_quantity -= $deductFromThis;
            $layer->save();

            $quantityToDeduct -= $deductFromThis;
        }

        // Eager load adjustment to get reference_no
        $item->load('adjustment');
        
        $movementData = [
            'product_id' => (int) $product->id,
            'type' => 'adjustment_out',
            'quantity' => (int) -$item->quantity,
            'reference_no' => (string) $item->adjustment->reference_no,
            'created_by' => auth()->id() ? (int) auth()->id() : null,
            'notes' => "Stock adjustment out - Reason: {$reason}",
        ];
        
        Log::info('=== Creating StockMovement ===', $movementData);

        try {
            StockMovement::create($movementData);
            Log::info('=== StockMovement created successfully ===');
        } catch (\Exception $e) {
            Log::error('=== StockMovement creation FAILED ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }

        $this->syncProductStock($product);
    }

    /**
     * Create layer from purchase (called by PurchaseController)
     */
    public function createPurchaseLayer(int $productId, int $purchaseId, int $quantity, int $unitCost): InventoryLayer
    {
        return InventoryLayer::create([
            'product_id' => $productId,
            'source_type' => InventoryLayer::SOURCE_PURCHASE,
            'source_id' => $purchaseId,
            'quantity' => $quantity,
            'remaining_quantity' => $quantity,
            'unit_cost' => $unitCost,
        ]);
    }

    /**
     * Deduct from layers for sale (called by SaleController)
     */
    public function deductForSale(int $productId, int $quantity, string $referenceNo): int
    {
        $layers = InventoryLayer::where('product_id', $productId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $totalAvailable = $layers->sum('remaining_quantity');

        if ($totalAvailable < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$totalAvailable}, Requested: {$quantity}");
        }

        $totalCost = 0;

        foreach ($layers as $layer) {
            if ($quantity <= 0) break;

            $deductFromThis = min($layer->remaining_quantity, $quantity);
            
            $layer->remaining_quantity -= $deductFromThis;
            $layer->save();

            $totalCost += $deductFromThis * $layer->unit_cost;
            $quantity -= $deductFromThis;
        }

        StockMovement::create([
            'product_id' => $productId,
            'type' => 'sale',
            'quantity' => -$totalAvailable + $layers->sum('remaining_quantity'),
            'reference_no' => $referenceNo,
            'created_by' => auth()->id(),
            'notes' => 'Stock sold',
        ]);

        $product = Product::find($productId);
        $this->syncProductStock($product);

        return $totalCost;
    }

    /**
     * Sync product stock from inventory_layers remaining_quantity
     */
    public function syncProductStock(Product $product): void
    {
        $totalStock = InventoryLayer::where('product_id', $product->id)
            ->sum('remaining_quantity');

        if ($product->stock !== $totalStock) {
            $product->stock = $totalStock;
            $product->save();
        }
    }

    /**
     * Get current stock for a product
     */
    public function getCurrentStock(int $productId): int
    {
        return InventoryLayer::where('product_id', $productId)
            ->sum('remaining_quantity');
    }

    /**
     * Get FIFO layers for a product
     */
    public function getFIFOLayers(int $productId)
    {
        return InventoryLayer::where('product_id', $productId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Calculate average cost for a product
     */
    public function getAverageCost(int $productId): int
    {
        $layers = $this->getFIFOLayers($productId);
        
        if ($layers->isEmpty()) {
            return 0;
        }

        $totalQuantity = $layers->sum('remaining_quantity');
        $totalCost = $layers->sum(fn($layer) => $layer->remaining_quantity * $layer->unit_cost);

        return $totalQuantity > 0 ? intval($totalCost / $totalQuantity) : 0;
    }
}
