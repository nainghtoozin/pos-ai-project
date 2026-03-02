<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreStockAdjustmentRequest;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $adjustments = StockAdjustment::with(['creator', 'items.product'])
            ->when($search, fn($q, $v) => $q->where('reference_no', 'like', "%{$v}%"))
            ->when($type, fn($q, $v) => $q->where('type', $v))
            ->when($dateFrom, fn($q, $v) => $q->whereDate('adjustment_date', '>=', $v))
            ->when($dateTo, fn($q, $v) => $q->whereDate('adjustment_date', '<=', $v))
            ->orderBy('adjustment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('stock_adjustments.index', compact('adjustments', 'search', 'type', 'dateFrom', 'dateTo'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $reasons = \App\Models\StockAdjustmentItem::REASONS;
        $referenceNo = StockAdjustment::generateReferenceNo();

        return view('stock_adjustments.create', compact('products', 'reasons', 'referenceNo'));
    }

    public function store(StoreStockAdjustmentRequest $request)
    {
        try {
            $adjustment = $this->inventoryService->handleAdjustment($request->validated());

            return redirect()
                ->route('stock_adjustments.index')
                ->with('success', 'Stock adjustment created successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['creator', 'items.product']);

        return view('stock_adjustments.show', compact('stockAdjustment'));
    }

    public function getProductStock(Request $request)
    {
        $productId = $request->get('product_id');
        
        $currentStock = $this->inventoryService->getCurrentStock($productId);
        $averageCost = $this->inventoryService->getAverageCost($productId);
        
        $product = Product::find($productId);

        return response()->json([
            'current_stock' => $currentStock,
            'average_cost' => $averageCost,
            'product_name' => $product?->name,
        ]);
    }
}
