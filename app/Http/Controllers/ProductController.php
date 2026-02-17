<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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

        $search = $request->get('search');

        $products = Product::with($this->withRelations)
            ->single()
            ->when($search, fn($q) =>
                $q->where('name', 'like', "%$search%")
                    ->orWhere('sku', 'like', "%$search%")
                    ->orWhere('barcode', 'like', "%$search%")
            )
            ->orderBy('id', 'desc')
            ->paginate(12);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($products);
        }

        return view('products.index', [
            'products' => $products,
            'filters' => [
                'search' => $search ?? '',
            ],
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

        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['product_type'] = 'single';
            $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($data);

            $product->stock()->create([
                'quantity' => 0,
            ]);
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
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $product->is_active;

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);
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
