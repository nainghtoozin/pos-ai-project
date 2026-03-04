<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryLayer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::with(['branch', 'customer', 'user'])
            ->when($request->branch_id, fn($q, $v) => $q->where('branch_id', $v))
            ->when($request->customer_id, fn($q, $v) => $q->where('customer_id', $v))
            ->when($request->status, fn($q, $v) => $q->where('payment_status', $v))
            ->when($request->date_from, fn($q, $v) => $q->whereDate('sale_date', '>=', $v))
            ->when($request->date_to, fn($q, $v) => $q->whereDate('sale_date', '<=', $v))
            ->orderBy('sale_date', 'desc')
            ->paginate(15);

        $branches = Branch::where('is_active', true)->get();
        $customers = Customer::orderBy('name')->get();

        return view('sales.index', compact('sales', 'branches', 'customers'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $customers = Customer::orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        
        $defaultBranch = $branches->firstWhere('is_default') ?? $branches->first();
        $invoiceNo = Sale::generateInvoiceNo();

        return view('pos.index', compact(
            'branches',
            'customers',
            'paymentMethods',
            'categories',
            'brands',
            'defaultBranch',
            'invoiceNo'
        ));
    }

    public function store(Request $request)
    {
        return $this->processSale($request, 'completed');
    }

    public function storeDraft(Request $request)
    {
        return $this->processSale($request, 'draft');
    }

    public function storeSuspended(Request $request)
    {
        return $this->processSale($request, 'suspended');
    }

    public function storeMultiplePayment(Request $request)
    {
        return $this->processSale($request, 'completed', true);
    }

    protected function processSale(Request $request, string $status, bool $multiplePayment = false)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0'],
            'discount_type' => ['nullable', 'in:fixed,percentage'],
            'tax' => ['nullable', 'integer', 'min:0'],
            'tax_type' => ['nullable', 'in:fixed,percentage'],
            'shipping' => ['nullable', 'integer', 'min:0'],
            'paid_amount' => ['nullable', 'integer', 'min:0'],
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payments' => ['nullable', 'array'],
            'payments.*.payment_method_id' => ['required_with:payments', 'integer', 'exists:payment_methods,id'],
            'payments.*.amount' => ['required_with:payments', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $result = DB::transaction(function () use ($validated, $status, $multiplePayment) {
                $branchId = $validated['branch_id'];
                $items = $validated['items'];
                
                $deductStock = in_array($status, ['completed']);
                $paidAmount = $validated['paid_amount'] ?? 0;
                
                if ($deductStock) {
                    foreach ($items as $item) {
                        $availableStock = BranchStock::where('branch_id', $branchId)
                            ->where('product_id', $item['product_id'])
                            ->value('quantity') ?? 0;

                        if ($availableStock < $item['quantity']) {
                            $product = Product::find($item['product_id']);
                            throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$availableStock}, Requested: {$item['quantity']}");
                        }
                    }
                }

                $subtotal = 0;
                $totalCost = 0;
                $totalProfit = 0;
                $saleItemsData = [];

                foreach ($items as $item) {
                    $product = Product::find($item['product_id']);
                    $quantity = $item['quantity'];
                    $unitPrice = $item['unit_price'];

                    $costPrice = $deductStock ? $this->calculateFIFOCost($product->id, $quantity) : 0;
                    $itemTotal = $quantity * $unitPrice;
                    $itemCost = $quantity * $costPrice;
                    $itemProfit = $itemTotal - $itemCost;

                    $subtotal += $itemTotal;
                    $totalCost += $itemCost;
                    $totalProfit += $itemProfit;

                    $saleItemsData[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'cost_price' => $costPrice,
                        'total' => $itemTotal,
                        'profit' => $itemProfit,
                    ];
                }

                $discountValue = $validated['discount'] ?? 0;
                $discountType = $validated['discount_type'] ?? 'fixed';
                $discountAmount = $discountType === 'percentage' ? ($subtotal * $discountValue / 100) : $discountValue;

                $taxValue = $validated['tax'] ?? 0;
                $taxType = $validated['tax_type'] ?? 'fixed';
                $taxAmount = $taxType === 'percentage' ? ($subtotal * $taxValue / 100) : $taxValue;

                $shippingAmount = $validated['shipping'] ?? 0;
                $grandTotal = $subtotal - $discountAmount + $taxAmount + $shippingAmount;

                if ($multiplePayment && isset($validated['payments'])) {
                    $paidAmount = array_sum(array_column($validated['payments'], 'amount'));
                }

                $dueAmount = max(0, $grandTotal - $paidAmount);

                $paymentStatus = match (true) {
                    $paidAmount >= $grandTotal => Sale::PAYMENT_PAID,
                    $paidAmount > 0 => Sale::PAYMENT_PARTIAL,
                    default => Sale::PAYMENT_DUE,
                };

                $sale = Sale::create([
                    'invoice_no' => Sale::generateInvoiceNo(),
                    'branch_id' => $branchId,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'user_id' => auth()->id(),
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'shipping' => $shippingAmount,
                    'grand_total' => $grandTotal,
                    'total_cost' => $totalCost,
                    'total_profit' => $totalProfit,
                    'paid_amount' => $paidAmount,
                    'due_amount' => $dueAmount,
                    'payment_status' => $paymentStatus,
                    'payment_method' => !$multiplePayment && ($validated['payment_method_id'] ?? null) 
                        ? PaymentMethod::find($validated['payment_method_id'])?->name 
                        : 'Multiple',
                    'status' => $status,
                    'suspended_at' => $status === 'suspended' ? now() : null,
                    'note' => $validated['note'] ?? null,
                    'sale_date' => now(),
                ]);

                foreach ($saleItemsData as $itemData) {
                    $saleItem = SaleItem::create([
                        'sale_id' => $sale->id,
                        ...$itemData,
                    ]);

                    if ($deductStock) {
                        $this->deductFromInventory($saleItem, $branchId, $sale->invoice_no);
                        $this->deductFromBranchStock($saleItem, $branchId);
                    }
                }

                if ($paidAmount > 0) {
                    if ($multiplePayment && isset($validated['payments'])) {
                        foreach ($validated['payments'] as $payment) {
                            SalePayment::create([
                                'sale_id' => $sale->id,
                                'payment_method_id' => $payment['payment_method_id'],
                                'amount' => $payment['amount'],
                                'created_by' => auth()->id(),
                            ]);
                        }
                    } else {
                        SalePayment::create([
                            'sale_id' => $sale->id,
                            'payment_method_id' => $validated['payment_method_id'] ?? null,
                            'amount' => $paidAmount,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }

                return [
                    'sale_id' => $sale->id,
                    'invoice_no' => $sale->invoice_no,
                    'status' => $status,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $paidAmount,
                    'due_amount' => $dueAmount,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => $status === 'completed' ? 'Sale completed successfully!' : 
                            ($status === 'draft' ? 'Sale saved as draft!' : 'Sale suspended!'),
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    protected function calculateFIFOCost(int $productId, int $quantity): int
    {
        $layers = InventoryLayer::where('product_id', $productId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($layers->isEmpty()) {
            return 0;
        }

        $totalCost = 0;
        $remainingQty = $quantity;

        foreach ($layers as $layer) {
            if ($remainingQty <= 0) break;

            $deductFromThis = min($layer->remaining_quantity, $remainingQty);
            $totalCost += $deductFromThis * $layer->unit_cost;
            $remainingQty -= $deductFromThis;
        }

        return $quantity > 0 ? intval($totalCost / $quantity) : 0;
    }

    protected function deductFromInventory(SaleItem $item, int $branchId, string $referenceNo): void
    {
        $productId = $item->product_id;
        $quantity = $item->quantity;

        $layers = InventoryLayer::where('product_id', $productId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $totalAvailable = $layers->sum('remaining_quantity');

        if ($totalAvailable < $quantity) {
            throw new \Exception("Insufficient inventory for product ID: {$productId}");
        }

        foreach ($layers as $layer) {
            if ($quantity <= 0) break;

            $deductFromThis = min($layer->remaining_quantity, $quantity);
            $layer->remaining_quantity -= $deductFromThis;
            $layer->save();

            $quantity -= $deductFromThis;
        }

        StockMovement::create([
            'product_id' => $productId,
            'type' => 'sale',
            'quantity' => -$item->quantity,
            'reference_no' => $referenceNo,
            'created_by' => auth()->id(),
            'notes' => 'Sale - Branch: ' . $branchId,
        ]);
    }

    protected function deductFromBranchStock(SaleItem $item, int $branchId): void
    {
        $branchStock = BranchStock::firstOrNew([
            'branch_id' => $branchId,
            'product_id' => $item->product_id,
        ]);

        $branchStock->quantity = ($branchStock->quantity ?? 0) - $item->quantity;
        $branchStock->save();
    }

    public function show(Sale $sale)
    {
        $sale->load(['branch', 'customer', 'user', 'items.product', 'payments']);

        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        if (!$sale->isCompleted()) {
            $sale->delete();
            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale deleted successfully!');
        }

        return DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $layers = InventoryLayer::where('product_id', $item->product_id)
                    ->orderBy('created_at', 'asc')
                    ->get();

                $remainingQty = $item->quantity;
                foreach ($layers as $layer) {
                    if ($remainingQty <= 0) break;
                    $layer->remaining_quantity += min($layer->quantity, $remainingQty);
                    $layer->save();
                    $remainingQty -= min($layer->quantity, $remainingQty);
                }

                $branchStock = BranchStock::where('branch_id', $sale->branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();
                
                if ($branchStock) {
                    $branchStock->quantity += $item->quantity;
                    $branchStock->save();
                }
            }

            $sale->delete();

            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale cancelled and stock restored!');
        });
    }
}
