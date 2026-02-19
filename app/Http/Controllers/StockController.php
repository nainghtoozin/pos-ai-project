<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseLine;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('product.view'), 403);

        $search = $request->get('search', '');
        $categoryId = $request->get('category_id');

        $products = Product::select('products.*')
            ->selectSub(
                ProductStock::select('current_stock')
                    ->whereColumn('product_id', 'products.id'),
                'current_stock'
            )
            ->selectSub(
                StockMovement::select(DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN type IN ("opening", "purchase", "sale_return", "transfer_in", "adjustment_in") THEN quantity
                        WHEN type IN ("sale", "purchase_return", "transfer_out", "adjustment_out") THEN -quantity
                        ELSE 0
                    END
                ), 0)'))
                    ->whereColumn('product_id', 'products.id'),
                'movement_stock'
            )
            ->with(['category', 'unit', 'stock'])
            ->when($search, fn($q) => $q->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            }))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderBy('products.name')
            ->paginate(20)
            ->withQueryString();

        $categories = \App\Models\Category::orderBy('name')->pluck('name', 'id');

        return view('stocks.index', compact('products', 'categories'));
    }

    public function show(Request $request, Product $product)
    {
        abort_unless(auth()->user()->can('product.view'), 403);

        $product->load(['category', 'brand', 'unit', 'stock']);

        $stockSummary = $this->getStockSummary($product->id);
        $movements = $this->getStockMovements($request, $product->id);

        return view('stocks.show', compact('product', 'stockSummary', 'movements'));
    }

    private function getStockSummary(int $productId): array
    {
        $stockMovements = StockMovement::where('product_id', $productId)
            ->select('type', DB::raw('SUM(quantity) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $openingFromPurchase = PurchaseLine::where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->sum('quantity');

        $opening = ($stockMovements[StockMovement::TYPE_OPENING] ?? 0) + $openingFromPurchase;

        return [
            'opening' => $opening,
            'purchase' => $stockMovements[StockMovement::TYPE_PURCHASE] ?? 0,
            'sale' => $stockMovements[StockMovement::TYPE_SALE] ?? 0,
            'sale_return' => $stockMovements[StockMovement::TYPE_SALE_RETURN] ?? 0,
            'purchase_return' => $stockMovements[StockMovement::TYPE_PURCHASE_RETURN] ?? 0,
            'transfer_in' => $stockMovements[StockMovement::TYPE_TRANSFER_IN] ?? 0,
            'transfer_out' => $stockMovements[StockMovement::TYPE_TRANSFER_OUT] ?? 0,
            'adjustment_in' => $stockMovements[StockMovement::TYPE_ADJUSTMENT_IN] ?? 0,
            'adjustment_out' => $stockMovements[StockMovement::TYPE_ADJUSTMENT_OUT] ?? 0,
            'stock_in' => $opening
                + ($stockMovements[StockMovement::TYPE_PURCHASE] ?? 0) 
                + ($stockMovements[StockMovement::TYPE_SALE_RETURN] ?? 0) 
                + ($stockMovements[StockMovement::TYPE_TRANSFER_IN] ?? 0) 
                + ($stockMovements[StockMovement::TYPE_ADJUSTMENT_IN] ?? 0),
            'stock_out' => ($stockMovements[StockMovement::TYPE_SALE] ?? 0) 
                + ($stockMovements[StockMovement::TYPE_PURCHASE_RETURN] ?? 0) 
                + ($stockMovements[StockMovement::TYPE_TRANSFER_OUT] ?? 0) 
                + ($stockMovements[StockMovement::TYPE_ADJUSTMENT_OUT] ?? 0),
        ];
    }

    private function getStockMovements(Request $request, int $productId)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $searchRef = $request->get('search');

        $movements = StockMovement::where('product_id', $productId)
            ->with('creator:id,name')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($searchRef, fn($q) => $q->where('reference_no', 'like', "%{$searchRef}%"))
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $purchaseLines = PurchaseLine::where('product_id', $productId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($line) {
                return (object) [
                    'id' => 'pl-' . $line->id,
                    'type' => 'purchase',
                    'type_label' => 'Purchase',
                    'reference_no' => $line->purchase_id ? 'PO-' . $line->purchase_id : null,
                    'quantity' => $line->quantity,
                    'created_at' => $line->created_at,
                    'creator' => null,
                    'is_purchase' => true,
                ];
            });

        $mergedMovements = $movements->concat($purchaseLines)
            ->sortBy('created_at')
            ->sortBy('id')
            ->values();

        $runningBalance = 0;
        
        return $mergedMovements->map(function ($movement) use (&$runningBalance) {
            if (isset($movement->is_purchase)) {
                $runningBalance += $movement->quantity;
            } elseif (in_array($movement->type, StockMovement::STOCK_IN)) {
                $runningBalance += $movement->quantity;
            } elseif (in_array($movement->type, StockMovement::STOCK_OUT)) {
                $runningBalance -= $movement->quantity;
            }
            
            $movement->running_balance = $runningBalance;
            $movement->in_qty = isset($movement->is_purchase) || in_array($movement->type ?? '', StockMovement::STOCK_IN) 
                ? $movement->quantity 
                : 0;
            $movement->out_qty = isset($movement->is_purchase) ? 0 : (in_array($movement->type ?? '', StockMovement::STOCK_OUT) ? $movement->quantity : 0);
            
            return $movement;
        });
    }
}
