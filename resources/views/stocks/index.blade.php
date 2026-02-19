@extends('layouts.app')

@section('title', 'Stock Management')
@section('page-title', 'Stock Management')

@section('content')
<div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-warehouse text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Stock Overview</h3>
                    <p class="text-sm text-gray-500">View all product stock levels</p>
                </div>
            </div>
            
            <form method="GET" action="{{ route('stocks.index') }}" class="flex flex-wrap gap-3">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search products..."
                        class="pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm
                               focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <div class="relative">
                    <select name="category_id" 
                        class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm
                               focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" 
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                    Filter
                </button>
                @if(request()->has('search') || request()->has('category_id'))
                    <a href="{{ route('stocks.index') }}" 
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 border border-gray-200 text-gray-600 font-medium rounded-lg transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Current Stock</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $product)
                @php
                    $stock = $product->current_stock ?? 0;
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-10 w-10 object-cover rounded-lg">
                            @else
                                <div class="h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-box text-gray-400"></i>
                                </div>
                            @endif
                            <span class="font-medium text-gray-800">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-500">{{ $product->sku }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-500">{{ $product->category->name ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-500">{{ $product->unit->short_name ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-semibold {{ $stock > 0 ? 'text-gray-800' : 'text-red-600' }}">
                            {{ \App\Helpers\NumberFormatter::format($stock) }}
                            {{ $product->unit->short_name ?? '' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($stock <= 0)
                            <span class="inline-flex items-center px-2.5 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                <i class="fas fa-times-circle mr-1"></i> Out of Stock
                            </span>
                        @elseif($stock <= 10)
                            <span class="inline-flex items-center px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded-full">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-full">
                                <i class="fas fa-check-circle mr-1"></i> In Stock
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('stocks.show', $product) }}" 
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-medium rounded-lg transition">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-warehouse text-gray-300 text-4xl"></i>
                            <p class="text-gray-500 font-medium">No products found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="p-6 border-t border-gray-100 bg-gray-50">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
