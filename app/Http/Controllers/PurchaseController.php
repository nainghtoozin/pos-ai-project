<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchasePaymentService;
use App\Services\PurchaseReturnService;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    protected $purchaseService;
    protected $paymentService;
    protected $returnService;

    public function __construct(
        PurchaseService $purchaseService,
        PurchasePaymentService $paymentService,
        PurchaseReturnService $returnService
    ) {
        $this->purchaseService = $purchaseService;
        $this->paymentService = $paymentService;
        $this->returnService = $returnService;
    }

    public function index(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();
        
        $purchases = Purchase::query()
            ->with(['supplier', 'creator'])
            ->when($request->supplier_id, fn($q, $val) => $q->where('supplier_id', $val))
            ->when($request->search, fn($q, $val) => $q->where('invoice_no', 'like', "%{$val}%"))
            ->when($request->date_from, fn($q, $val) => $q->whereDate('created_at', '>=', $val))
            ->when($request->date_to, fn($q, $val) => $q->whereDate('created_at', '<=', $val))
            ->when($request->payment_status, fn($q, $val) => $q->where('payment_status', $val))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get();
        $defaultPaymentMethod = \App\Models\PaymentMethod::systemDefault()->first();

        return view('purchases.index', compact('purchases', 'paymentMethods', 'suppliers', 'defaultPaymentMethod'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();
        $defaultPaymentMethod = PaymentMethod::systemDefault()->first();
        $statuses = Purchase::STATUSES;
        $purchase = new Purchase();
        $purchaseItems = [];
        
        return view('purchases.create', compact('suppliers', 'paymentMethods', 'defaultPaymentMethod', 'statuses', 'purchase', 'purchaseItems'));
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
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();
        $defaultPaymentMethod = PaymentMethod::systemDefault()->first();
        $statuses = Purchase::STATUSES;
        
        $purchaseItems = $purchase->lines->map(function($line) {
            return [
                'product_id' => $line->product_id,
                'name' => $line->product->name,
                'sku' => $line->product->sku,
                'quantity' => $line->quantity,
                'purchase_price' => $line->purchase_price,
                'selling_price' => $line->selling_price,
                'discount_amount' => $line->discount_amount,
                'line_total' => $line->line_total,
            ];
        })->toArray();
        
        return view('purchases.edit', compact('purchase', 'suppliers', 'paymentMethods', 'defaultPaymentMethod', 'statuses', 'purchaseItems'));
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

    public function print(Purchase $purchase)
    {
        $purchase->load(['supplier', 'lines.product', 'payments', 'returns.items.product', 'creator']);
        
        return view('purchases.print', compact('purchase'));
    }

    public function addPayment(Request $request, Purchase $purchase)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $purchase->due_amount,
            'payment_date' => 'required|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'note' => 'nullable|string',
        ]);

        try {
            $this->paymentService->createPayment($purchase, $request->all());
            return redirect()->back()->with('success', 'Payment added successfully!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function createReturn(Purchase $purchase)
    {
        $purchase->load(['lines.product', 'returns.items']);
        
        return view('purchases.return', compact('purchase'));
    }

    public function storeReturn(Request $request, Purchase $purchase)
    {
        $request->validate([
            'return_date' => 'required|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.return_price' => 'required|numeric|min:0',
        ]);

        try {
            $this->returnService->createReturn($purchase, $request->all());
            return redirect()->route('purchases.index')->with('success', 'Purchase return created successfully!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}
