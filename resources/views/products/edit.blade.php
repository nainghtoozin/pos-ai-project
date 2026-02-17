@extends('layouts.app')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
<div class="min-h-screen bg-gray-100 py-10">
    <div class="mx-auto max-w-5xl">
        <div class="rounded-2xl bg-white p-8 shadow-md">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
                    <p class="text-sm text-gray-500">SKU {{ $product->sku }} · Barcode {{ $product->barcode }}</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Back to list</a>
            </div>

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Product Type</label>
                        <select class="mt-2 w-full rounded-xl border-gray-200 text-sm" disabled>
                            <option value="single" selected>Single</option>
                            <option disabled>Variable (soon)</option>
                            <option disabled>Combo (soon)</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required autocomplete="off"
                               class="mt-2 w-full rounded-xl border-gray-200 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label for="barcode" class="block text-sm font-semibold text-gray-700">Barcode</label>
                        <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}" required autocomplete="off"
                               class="mt-2 w-full rounded-xl border-gray-200 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('barcode')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="sku" class="block text-sm font-semibold text-gray-700">SKU</label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required autocomplete="off"
                               class="mt-2 w-full rounded-xl border-gray-200 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('sku')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            Active
                        </label>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-700">Category</label>
                        <select id="category_id" name="category_id" required class="mt-2 w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}" @selected(old('category_id', $product->category_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="brand_id" class="block text-sm font-semibold text-gray-700">Brand</label>
                        <select id="brand_id" name="brand_id" required class="mt-2 w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($brands as $id => $name)
                                <option value="{{ $id }}" @selected(old('brand_id', $product->brand_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="unit_id" class="block text-sm font-semibold text-gray-700">Unit</label>
                        <select id="unit_id" name="unit_id" required class="mt-2 w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($units as $id => $name)
                                <option value="{{ $id }}" @selected(old('unit_id', $product->unit_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('unit_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="tax_id" class="block text-sm font-semibold text-gray-700">Tax (optional)</label>
                        <select id="tax_id" name="tax_id" class="mt-2 w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">No tax</option>
                            @foreach($taxes as $id => $name)
                                <option value="{{ $id }}" @selected(old('tax_id', $product->tax_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('tax_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Description</label>
                    <input id="description" type="hidden" name="description" value="{{ old('description', $product->description) }}">
                    <trix-editor input="description" class="mt-2 rounded-xl border border-gray-200 bg-white"></trix-editor>
                    @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="image" class="block text-sm font-semibold text-gray-700">Product Image</label>
                        <input type="file" id="image" name="image" class="mt-2 block w-full rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('image')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-end">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-28 w-28 rounded-xl object-cover shadow">
                        @else
                            <div class="text-sm text-gray-500">No image uploaded.</div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('products.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-indigo-700">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite('resources/js/modules/wysiwyg.js')
@endsection
