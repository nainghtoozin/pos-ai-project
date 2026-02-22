<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'creator'])
            ->select('purchases.*')
            ->orderBy('purchases.created_at', 'desc')
            ->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $statuses = Purchase::STATUSES;
        $purchase = new Purchase();
        
        return view('purchases.create', compact('suppliers', 'statuses', 'purchase'));
    }

    public function store(StorePurchaseRequest $request)
    {
        $purchase = $this->purchaseService->createPurchase($request->validated());
        
        return redirect()->route('purchases.index')->with('success', 'Purchase created successfully!');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'lines.product', 'creator']);
        
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load(['supplier', 'lines.product']);
        $suppliers = Supplier::orderBy('name')->get();
        $statuses = Purchase::STATUSES;
        
        return view('purchases.edit', compact('purchase', 'suppliers', 'statuses'));
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        $purchase = $this->purchaseService->updatePurchase($purchase, $request->validated());
        
        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === Purchase::STATUS_RECEIVED) {
            foreach ($purchase->lines as $line) {
                $stock = \App\Models\ProductStock::where('product_id', $line->product_id)->first();
                if ($stock) {
                    $stock->current_stock = max(0, $stock->current_stock - $line->quantity);
                    $stock->save();
                }
                
                \App\Models\StockMovement::where('reference_no', $purchase->id)
                    ->where('product_id', $line->product_id)
                    ->where('type', \App\Models\StockMovement::TYPE_PURCHASE)
                    ->delete();
            }
        }
        
        $purchase->lines()->delete();
        $purchase->delete();
        
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully!');
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }
        
        $products = Product::where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'sku', 'barcode', 'sale_price', 'purchase_price')
            ->with('stock') // Avoid N+1
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function getProduct(Request $request)
    {
        $product = Product::with(['stock', 'category', 'brand', 'unit'])
            ->where('id', $request->product_id)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }
}
