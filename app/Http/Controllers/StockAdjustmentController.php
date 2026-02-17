<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function getLatestPurchasePrice(int $productId): JsonResponse
    {
        $latestCost = PurchaseLine::where('product_id', $productId)
            ->where('remaining_qty', '>', 0)
            ->orderBy('id', 'desc')
            ->value('cost_price');

        return response()->json([
            'purchase_price' => $latestCost ?? 0,
        ]);
    }

    public function adjust(Request $request, Product $product): JsonResponse
    {
        abort_unless(auth()->user()->can('product.edit'), 403);

        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:increase,decrease'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $quantity = (float) $validated['quantity'];
        $costPrice = (float) $validated['cost_price'];
        $type = $validated['type'];
        $note = $validated['note'] ?? null;

        $currentStock = ProductStock::where('product_id', $product->id)->value('current_stock') ?? 0;

        if ($type === 'decrease' && $quantity > $currentStock) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock. Available: {$currentStock}",
            ], 422);
        }

        if ($type === 'decrease' && $quantity > $currentStock) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot decrease more stock than available.',
            ], 422);
        }

        DB::transaction(function () use ($product, $quantity, $costPrice, $type, $note, $currentStock) {
            if ($type === 'increase') {
                $purchase = Purchase::create([
                    'supplier_id' => null,
                    'purchase_date' => now()->toDateString(),
                    'total_amount' => $quantity * $costPrice,
                    'notes' => 'Stock increase: ' . $product->name . ($note ? " - {$note}" : ''),
                    'status' => 'received',
                ]);

                PurchaseLine::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'cost_price' => $costPrice,
                    'remaining_qty' => $quantity,
                ]);

                ProductStock::updateOrCreate(
                    ['product_id' => $product->id],
                    []
                )->increment('current_stock', $quantity);
            } else {
                $this->deductFromFIFO($product->id, $quantity);
            }
        });

        $newStock = ProductStock::where('product_id', $product->id)->value('current_stock') ?? 0;

        return response()->json([
            'success' => true,
            'message' => $type === 'increase' ? 'Stock increased successfully.' : 'Stock decreased successfully.',
            'new_stock' => $newStock,
        ]);
    }

    private function deductFromFIFO(int $productId, float $quantity): void
    {
        $remainingToDeduct = $quantity;

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
            $line->decrement('remaining_qty', $deductFromLine);
            $remainingToDeduct -= $deductFromLine;
        }

        if ($remainingToDeduct > 0) {
            throw new \RuntimeException("FIFO calculation error: insufficient purchase lines");
        }

        ProductStock::where('product_id', $productId)->decrement('current_stock', $quantity);
    }
}
