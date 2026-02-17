@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Product Management')

@section('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
@php
    $canEdit = auth()->user()?->can('product.edit');
    $canDelete = auth()->user()?->can('product.delete');
@endphp

<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl bg-white p-6 shadow-md">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold text-gray-900">Products</h1>
                <p class="text-sm text-gray-500">Single products only. Variable & combo types coming soon.</p>
            </div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                @can('product.create')
                    <a href="{{ route('products.create') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                        + Add Product
                    </a>
                @endcan
                <div class="relative">
                    <input type="text" id="product-search" value="{{ $filters['search'] ?? '' }}"
                           data-search-url="{{ route('products.index') }}"
                           data-products-base="{{ url('products') }}"
                           data-can-edit="{{ $canEdit ? '1' : '0' }}"
                           data-can-delete="{{ $canDelete ? '1' : '0' }}"
                           class="w-full rounded-full border border-gray-200 bg-gray-50 py-3 pl-5 pr-12 text-sm shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                           placeholder="Search name / SKU / barcode">
                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15z" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-8 overflow-hidden rounded-2xl border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Image</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">SKU</th>
                            <th class="px-6 py-4">Barcode</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Brand</th>
                            <th class="px-6 py-4">Stock</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-table" class="divide-y divide-gray-100 bg-white text-gray-700">
                        @forelse($products as $product)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="h-14 w-14 overflow-hidden rounded-lg bg-gray-100">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-xs text-gray-400">No Image</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-400">#{{ $product->id }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs uppercase text-gray-600">{{ $product->sku }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $product->barcode }}</td>
                                <td class="px-6 py-4">{{ $product->category->name ?? '--' }}</td>
                                <td class="px-6 py-4">{{ $product->brand->name ?? '--' }}</td>
                                <td class="px-6 py-4 font-semibold">{{ number_format($product->stock->quantity ?? 0, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" class="rounded-full border border-gray-200 p-2 text-gray-600 hover:border-indigo-200 hover:text-indigo-600"
                                                data-action="view"
                                                data-product="{{ base64_encode(json_encode([
                                                    'name' => $product->name,
                                                    'sku' => $product->sku,
                                                    'barcode' => $product->barcode,
                                                    'category' => $product->category->name ?? null,
                                                    'brand' => $product->brand->name ?? null,
                                                    'unit' => $product->unit->name ?? null,
                                                    'stock' => number_format($product->stock->quantity ?? 0, 2),
                                                    'image_url' => $product->image_url,
                                                    'description' => $product->description,
                                                    'status' => $product->is_active ? 'Active' : 'Inactive',
                                                ])) }}">
                                            <span class="sr-only">View</span>
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12 18 19.5 12 19.5 2.25 12 2.25 12z" />
                                                <circle cx="12" cy="12" r="2.25" />
                                            </svg>
                                        </button>
                                        @can('product.edit')
                                            <a href="{{ route('products.edit', $product) }}" class="rounded-full border border-gray-200 p-2 text-gray-600 hover:border-indigo-200 hover:text-indigo-600">
                                                <span class="sr-only">Edit</span>
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232a2.5 2.5 0 1 1 3.536 3.536L8.5 19.036l-4 1 1-4 9.732-10.804z" />
                                                </svg>
                                            </a>
                                        @endcan
                                        @can('product.delete')
                                            <button type="button" class="rounded-full border border-gray-200 p-2 text-gray-600 hover:border-rose-200 hover:text-rose-600"
                                                    data-action="delete"
                                                    data-delete-url="{{ route('products.destroy', $product) }}"
                                                    data-delete-name="{{ $product->name }}">
                                                <span class="sr-only">Delete</span>
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 6h-15m3 0V4.5A1.5 1.5 0 0 1 9 3h6a1.5 1.5 0 0 1 1.5 1.5V6m1.5 0v12.75A1.25 1.25 0 0 1 16.75 20H7.25A1.25 1.25 0 0 1 6 18.75V6" />
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="products-pagination" class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

@include('products.partials.view-modal')
@include('products.partials.delete-modal')
@endsection

@section('scripts')
    @vite('resources/js/modules/product-search.js')
@endsection
