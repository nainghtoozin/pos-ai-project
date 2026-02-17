<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private array $withRelations = ['category', 'brand', 'unit', 'stock', 'tax'];

    public function index(Request $request): \Illuminate\Contracts\View\View|JsonResponse
    {
        abort_unless(auth()->user()->can('product.view'), 403);

        $filters = [
            'search' => $request->get('search', ''),
            'category_id' => $request->get('category_id'),
            'brand_id' => $request->get('brand_id'),
            'stock_status' => $request->get('stock_status'),
            'is_active' => $request->get('is_active'),
        ];

        $lowStockThreshold = 10;

        $productIds = Product::single()
            ->when($filters['search'], fn($q) => $q->where(function($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('sku', 'like', "%{$filters['search']}%")
                    ->orWhere('barcode', 'like', "%{$filters['search']}%");
            }))
            ->when($filters['category_id'], fn($q) => $q->where('category_id', $filters['category_id']))
            ->when($filters['brand_id'], fn($q) => $q->where('brand_id', $filters['brand_id']))
            ->when($filters['is_active'] !== null && $filters['is_active'] !== '', fn($q) => 
                $q->where('is_active', $filters['is_active'])
            )
            ->orderBy('id', 'desc')
            ->pluck('id');

        $stockData = ProductStock::whereIn('product_id', $productIds)
            ->pluck('current_stock', 'product_id')
            ->toArray();

        if ($filters['stock_status']) {
            $productIds = Product::single()
                ->when($filters['search'], fn($q) => $q->where(function($query) use ($filters) {
                    $query->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('sku', 'like', "%{$filters['search']}%")
                        ->orWhere('barcode', 'like', "%{$filters['search']}%");
                }))
                ->when($filters['category_id'], fn($q) => $q->where('category_id', $filters['category_id']))
                ->when($filters['brand_id'], fn($q) => $q->where('brand_id', $filters['brand_id']))
                ->when($filters['is_active'] !== null && $filters['is_active'] !== '', fn($q) => $q->where('is_active', $filters['is_active']))
                ->get()
                ->filter(function ($product) use ($stockData, $filters, $lowStockThreshold) {
                    $stock = $stockData[$product->id] ?? 0;
                    return match($filters['stock_status']) {
                        'in_stock' => $stock > 0,
                        'out_of_stock' => $stock <= 0,
                        'low_stock' => $stock > 0 && $stock <= $lowStockThreshold,
                        default => true,
                    };
                })
                ->pluck('id');
        }

        $products = Product::with(['category', 'brand', 'unit', 'tax'])
            ->whereIn('id', $productIds)
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->transform(function ($product) use ($stockData) {
            $product->current_stock = $stockData[$product->id] ?? 0;
            return $product;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($products);
        }

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
            'brands' => Brand::orderBy('name')->pluck('name', 'id'),
            'lowStockThreshold' => $lowStockThreshold,
        ]);
    }

    public function create(): \Illuminate\Contracts\View\View
    {
        abort_unless(auth()->user()->can('product.create'), 403);

        return view('products.create', $this->formData());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('product.create'), 403);

        $validated = $request->validated();
        
        $openingStock = $validated['opening_stock'] ?? 0;
        $purchasePrice = $validated['purchase_price'] ?? null;

        DB::transaction(function () use ($validated, $request, $openingStock, $purchasePrice) {
            $sku = $validated['sku'] ?? null;
            if (empty($sku)) {
                $sku = $this->generateUniqueSku();
            }

            $productData = [
                'name' => $validated['name'],
                'barcode' => $validated['barcode'] ?? null,
                'sku' => $sku,
                'product_type' => 'single',
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'unit_id' => $validated['unit_id'],
                'tax_id' => $validated['tax_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'sale_price' => $validated['sale_price'],
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            ];

            if ($request->hasFile('image')) {
                $productData['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($productData);

            if ($openingStock > 0 && $purchasePrice !== null) {
                $purchase = Purchase::create([
                    'supplier_id' => null,
                    'purchase_date' => now()->toDateString(),
                    'total_amount' => $openingStock * $purchasePrice,
                    'notes' => 'Opening stock for product: ' . $product->name,
                    'status' => 'received',
                ]);

                PurchaseLine::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $openingStock,
                    'cost_price' => $purchasePrice,
                    'remaining_qty' => $openingStock,
                ]);

                ProductStock::updateOrCreate(
                    ['product_id' => $product->id],
                    ['current_stock' => $openingStock]
                );
            } else {
                ProductStock::updateOrCreate(
                    ['product_id' => $product->id],
                    ['current_stock' => 0]
                );
            }
        });

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless(auth()->user()->can('product.view'), 403);

        $product->load($this->withRelations);

        return response()->json($product);
    }

    public function edit(Product $product): \Illuminate\Contracts\View\View
    {
        abort_unless(auth()->user()->can('product.edit'), 403);

        $product->load($this->withRelations);

        return view('products.edit', $this->formData() + compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        abort_unless(auth()->user()->can('product.edit'), 403);

        DB::transaction(function () use ($request, $product) {
            $validated = $request->validated();

            $sku = $validated['sku'] ?? null;
            if (empty($sku)) {
                $sku = $this->generateUniqueSku($product->id);
            }
            $validated['sku'] = $sku;

            $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $product->is_active;

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($validated);
        });

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        abort_unless(auth()->user()->can('product.delete'), 403);

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function updateOpeningStock(Request $request, Product $product): JsonResponse
    {
        abort_unless(auth()->user()->can('product.edit'), 403);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
            'type' => ['required', 'in:addition,deduction'],
        ]);

        $quantity = $request->integer('quantity');
        $purchasePrice = $request->input('purchase_price');
        $type = $request->input('type');
        $note = $request->input('note');

        $currentStock = ProductStock::where('product_id', $product->id)->value('current_stock') ?? 0;

        // If deduction, validate stock availability
        if ($type === 'deduction' && $quantity > $currentStock) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deduct more stock than available. Current stock: ' . $currentStock,
            ], 422);
        }

        DB::transaction(function () use ($product, $quantity, $purchasePrice, $type, $note) {
            $purchase = Purchase::create([
                'supplier_id' => null,
                'purchase_date' => now()->toDateString(),
                'total_amount' => $quantity * $purchasePrice,
                'notes' => ($type === 'deduction' ? 'Stock deduction' : 'Opening stock') . ' for product: ' . $product->name . ($note ? ' - ' . $note : ''),
                'status' => 'received',
            ]);

            $effectiveQty = $type === 'deduction' ? -$quantity : $quantity;

            PurchaseLine::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'cost_price' => $purchasePrice,
                'remaining_qty' => $effectiveQty,
            ]);

            $newStock = ($currentStock ?? 0) + $effectiveQty;

            ProductStock::updateOrCreate(
                ['product_id' => $product->id],
                ['current_stock' => $newStock]
            );
        });

        return response()->json([
            'success' => true,
            'message' => $type === 'deduction' ? 'Stock deducted successfully.' : 'Stock added successfully.',
            'new_stock' => ProductStock::where('product_id', $product->id)->value('current_stock') ?? 0,
        ]);
    }

    public function getLatestPurchasePrice(Product $product): JsonResponse
    {
        $latestCost = PurchaseLine::where('product_id', $product->id)
            ->where('remaining_qty', '>', 0)
            ->orderBy('id', 'desc')
            ->value('cost_price');

        return response()->json([
            'purchase_price' => $latestCost ?? 0,
        ]);
    }

    private function generateUniqueSku(?int $excludeProductId = null): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $latestProduct = Product::when($excludeProductId, fn($q) => $q->where('id', '!=', $excludeProductId))
                ->orderBy('id', 'desc')
                ->first();

            $nextId = $latestProduct ? $latestProduct->id + 1 : 1;
            $sku = 'PRD' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);

            $exists = Product::when($excludeProductId, fn($q) => $q->where('id', '!=', $excludeProductId))
                ->where('sku', $sku)
                ->exists();

            $attempt++;

            if (!$exists) {
                return $sku;
            }
        } while ($attempt < $maxAttempts);

        return 'PRD' . str_pad((string) (time() % 100000), 5, '0', STR_PAD_LEFT) . rand(10, 99);
    }

    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
            'brands' => Brand::orderBy('name')->pluck('name', 'id'),
            'units' => Unit::orderBy('name')->pluck('name', 'id'),
            'taxes' => Tax::orderBy('name')->pluck('name', 'id'),
        ];
    }
}
