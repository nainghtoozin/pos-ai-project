@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Product Management')

@section('styles')
<style>
[x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
<div x-data="productModalManager()">
<div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-boxes text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Products</h3>
                    <p class="text-sm text-gray-500">Manage your product inventory</p>
                </div>
            </div>
            @can('product.create')
                <a href="{{ route('products.create') }}"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition duration-200 transform hover:scale-[1.02] shadow-sm">
                    <i class="fas fa-plus"></i>Add Product
                </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center gap-2 text-green-700">
                    <i class="fas fa-check-circle"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="max-w-2xl">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                        placeholder="Search products by name or SKU..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                               focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                               hover:border-indigo-400 transition duration-200">
                </div>
                <div class="relative w-full sm:w-48">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-tags"></i>
                    </span>
                    <select name="category_id" 
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                               focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                               hover:border-indigo-400 transition duration-200">
                        <option value="">All Categories</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ ($filters['category_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative w-full sm:w-40">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-cube"></i>
                    </span>
                    <select name="brand_id" 
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                               focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                               hover:border-indigo-400 transition duration-200">
                        <option value="">All Brands</option>
                        @foreach($brands as $id => $name)
                            <option value="{{ $id }}" {{ ($filters['brand_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative w-full sm:w-40">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    <select name="stock_status" 
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                               focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                               hover:border-indigo-400 transition duration-200">
                        <option value="">All Stock</option>
                        <option value="in_stock" {{ ($filters['stock_status'] ?? '') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ ($filters['stock_status'] ?? '') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ ($filters['stock_status'] ?? '') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <button type="submit" 
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
                    Filter
                </button>
                @if(request()->has('search') || request()->has('category_id') || request()->has('brand_id') || request()->has('stock_status'))
                    <a href="{{ route('products.index') }}" 
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition duration-200 text-center">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Purchase Price</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Sale Price</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                @php
                    $stock = $product->current_stock ?? 0;
                    $viewData = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                        'category' => $product->category->name ?? null,
                        'brand' => $product->brand->name ?? null,
                        'unit' => $product->unit->name ?? null,
                        'stock' => number_format($stock, 2),
                        'image_url' => $product->image_url,
                        'description' => $product->description,
                        'status' => $product->is_active ? 'Active' : 'Inactive',
                        'sale_price' => $product->sale_price,
                        'purchase_price' => $product->latestPurchase ? $product->latestPurchase->cost_price : null,
                    ];
                    $openingStockData = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'current_stock' => $stock,
                    ];
                @endphp
                <tr class="even:bg-gray-50 hover:bg-indigo-50 transition duration-150">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-12 w-12 object-cover rounded-md">
                            @else
                                <div class="h-12 w-12 bg-gray-200 rounded-md flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                            @endif
                            <span class="text-sm font-semibold text-gray-800">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-600">{{ $product->sku }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-600">{{ $product->category->name ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($stock <= 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                <i class="fas fa-times-circle"></i>
                                {{ number_format($stock, 0) }}
                            </span>
                        @elseif($stock <= $lowStockThreshold)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded-full">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ number_format($stock, 0) }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                <i class="fas fa-check-circle"></i>
                                {{ number_format($stock, 0) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm text-gray-600">${{ number_format($product->purchase_price ?? 0, 2) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-semibold text-gray-800">${{ number_format($product->sale_price ?? 0, 2) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($product->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                <i class="fas fa-check-circle"></i>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                                <i class="fas fa-times-circle"></i>
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                @click.outside="open = false" 
                                type="button" 
                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50"
                            >
                                <i class="fas fa-cog"></i> Actions
                            </button>
                            <div 
                                x-show="open" 
                                x-cloak
                                class="absolute right-0 z-50 mt-1 w-48 rounded-lg border border-gray-100 bg-white py-1 shadow-lg"
                            >
                                <button 
                                    type="button"
                                    @click="viewProduct({{ json_encode($viewData) }}); open = false"
                                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                >
                                    <i class="fas fa-eye w-4 text-gray-400"></i> View
                                </button>
                                @can('product.edit')
                                    <a 
                                        href="{{ route('products.edit', $product) }}" 
                                        class="flex items-center gap-2 px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                    >
                                        <i class="fas fa-edit w-4 text-gray-400"></i> Edit
                                    </a>
                                    <button 
                                        type="button"
                                        @click="openStockModal({{ json_encode($openingStockData) }}); open = false"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                    >
                                        <i class="fas fa-plus-circle w-4 text-gray-400"></i> Opening Stock
                                    </button>
                                    <button 
                                        type="button"
                                        @click="openGroupPriceModal({{ json_encode(['id' => $product->id, 'name' => $product->name, 'sale_price' => $product->sale_price ?? 0]) }}); open = false"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                    >
                                        <i class="fas fa-tags w-4 text-gray-400"></i> Group Price
                                    </button>
                                @endcan
                                @can('product.delete')
                                    <div class="border-t border-gray-100"></div>
                                    <button 
                                        type="button"
                                        @click="deleteProduct({{ json_encode(['id' => $product->id, 'name' => $product->name, 'delete_url' => route('products.destroy', $product)]) }}); open = false"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                                    >
                                        <i class="fas fa-trash w-4"></i> Delete
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-box-open text-gray-300 text-4xl"></i>
                            <p class="text-gray-500 font-medium">No products found</p>
                            <p class="text-gray-400 text-sm">Try adjusting your search criteria</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="p-6 border-t border-gray-200 bg-gray-50">
        {{ $products->links() }}
    </div>
    @endif
</div>

<!-- View Modal -->
<div x-show="showViewModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
        <div x-show="showViewModal" @click.outside="closeModals()" class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Product Details</h3>
                <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex gap-4 mb-4">
                <div class="h-24 w-24 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                    <img x-show="viewProductData.image_url" :src="viewProductData.image_url" class="h-full w-full object-cover">
                    <i x-show="!viewProductData.image_url" class="fas fa-image text-gray-400 text-2xl"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-xl font-bold text-gray-800" x-text="viewProductData.name"></h4>
                    <p class="text-sm text-gray-500">SKU: <span x-text="viewProductData.sku"></span></p>
                    <p class="text-sm text-gray-500">Barcode: <span x-text="viewProductData.barcode || '-'"></span></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Category</p>
                    <p class="font-medium text-gray-800" x-text="viewProductData.category || '-'"></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Brand</p>
                    <p class="font-medium text-gray-800" x-text="viewProductData.brand || '-'"></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Current Stock</p>
                    <p class="font-medium text-gray-800" x-text="viewProductData.stock"></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Sale Price</p>
                    <p class="font-medium text-gray-800">$<span x-text="parseFloat(viewProductData.sale_price || 0).toFixed(2)"></span></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Status</p>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full"
                        :class="viewProductData.status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                        <span x-text="viewProductData.status"></span>
                    </span>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Unit</p>
                    <p class="font-medium text-gray-800" x-text="viewProductData.unit || '-'"></p>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-xs text-gray-500">Description</p>
                <p class="text-sm text-gray-700 mt-1" x-text="viewProductData.description || 'No description available'"></p>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" @click="closeModals()" 
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Wholesale Price Modal -->
<div x-show="showWholesaleModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
        <div x-show="showWholesaleModal" @click.outside="closeModals()" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Set Wholesale Price</h3>
                <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="" id="wholesaleForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Product</label>
                        <p class="text-gray-800 font-medium" x-text="wholesaleProductName"></p>
                    </div>
                    <div>
                        <label for="wholesale_price" class="block text-sm font-semibold text-gray-700">Wholesale Price <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                            <input type="number" name="wholesale_price" id="wholesale_price" step="0.01" min="0" 
                                class="w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                       focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                required>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="closeModals()" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
                        Save Price
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
        <div x-show="showDeleteModal" @click.outside="closeModals()" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Confirm Delete</h3>
                <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="py-4">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <p class="text-center text-gray-600">Are you sure you want to delete this product?</p>
                <p class="text-center text-gray-500 text-sm mt-2"><span x-text="deleteProductName" class="font-semibold"></span></p>
            </div>
            <form method="POST" action="" id="deleteForm">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeModals()" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Group Price Modal -->
<div x-show="showGroupPriceModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
        <div x-show="showGroupPriceModal" @click.outside="closeModals()" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Group Price</h3>
                <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Product</label>
                    <p class="mt-1 text-gray-900 font-semibold" x-text="groupPriceProductName"></p>
                </div>
                <div>
                    <label for="group_price" class="block text-sm font-medium text-gray-700">Price <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                        <input type="number" name="group_price" id="group_price" step="0.01" min="0"
                            class="mt-1 w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            required>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="closeModals()" 
                    class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition duration-200">
                    Cancel
                </button>
                <button type="button" @click="closeModals()" 
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
function productModalManager() {
    return {
        showViewModal: false,
        showStockModal: false,
        showGroupPriceModal: false,
        showWholesaleModal: false,
        showDeleteModal: false,
        viewProductData: {},
        stockProductName: '',
        stockCurrentStock: '',
        stockPurchasePrice: '',
        stockPriceHint: false,
        groupPriceProductName: '',
        wholesaleProductName: '',
        deleteProductName: '',

        viewProduct(product) {
            this.viewProductData = product;
            this.showViewModal = true;
        },

        async openStockModal(product) {
            this.stockProductName = product.name;
            this.stockCurrentStock = product.current_stock;
            this.stockPurchasePrice = '';
            this.stockPriceHint = false;
            
            document.getElementById('stockForm').action = '/products/' + product.id + '/opening-stock';
            
            try {
                const response = await fetch('/products/' + product.id + '/latest-purchase-price');
                const data = await response.json();
                this.stockPurchasePrice = data.purchase_price || '0';
                this.stockPriceHint = parseFloat(data.purchase_price) > 0;
            } catch (e) {
                this.stockPurchasePrice = '0';
                this.stockPriceHint = false;
            }
            
            this.showStockModal = true;
        },

        openGroupPriceModal(product) {
            this.groupPriceProductName = product.name;
            document.getElementById('group_price').value = product.sale_price || 0;
            this.showGroupPriceModal = true;
        },

        openWholesaleModal(product) {
            this.wholesaleProductName = product.name;
            document.getElementById('wholesaleForm').action = '/products/' + product.id + '/wholesale';
            document.getElementById('wholesale_price').value = product.wholesale_price || 0;
            this.showWholesaleModal = true;
        },

        deleteProduct(product) {
            this.deleteProductName = product.name;
            document.getElementById('deleteForm').action = product.delete_url;
            this.showDeleteModal = true;
        },

        closeModals() {
            this.showViewModal = false;
            this.showStockModal = false;
            this.showGroupPriceModal = false;
            this.showWholesaleModal = false;
            this.showDeleteModal = false;
        }
    }
}
</script>
@endsection
