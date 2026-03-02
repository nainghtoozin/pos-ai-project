@extends('layouts.app')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
    <div class="max-w-5xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
                <p class="text-sm text-gray-500 mt-1">SKU: {{ $product->sku }} · Barcode: {{ $product->barcode ?? 'N/A' }}
                </p>
            </div>
            <a href="{{ route('products.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-1"></i> Back to list
            </a>
        </div>

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 space-y-6">
                    <!-- Row 1: Name -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-3">
                            <label for="name"
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Product
                                Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                                required autocomplete="off"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @error('name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Status</label>
                            <label class="flex items-center gap-3 cursor-pointer h-full">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <!-- Row 2: SKU & Barcode -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sku"
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">SKU</label>
                            <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}"
                                required autocomplete="off"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @error('sku')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-data="{ hasBarcode: {{ $product->barcode ? 'true' : 'false' }} }">
                            <label for="barcode"
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Barcode</label>
                            <div class="flex gap-2">
                                <label class="flex items-center">
                                    <input type="hidden" name="has_barcode" value="0">
                                    <input type="checkbox" x-model="hasBarcode" name="has_barcode" value="1"
                                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2"
                                        {{ $product->barcode ? 'checked' : '' }}>
                                </label>
                                <input type="text" id="barcode" name="barcode"
                                    value="{{ old('barcode', $product->barcode) }}" :required="hasBarcode"
                                    :disabled="!hasBarcode" :class="!hasBarcode ? 'bg-gray-100' : 'bg-gray-50'"
                                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none disabled:bg-gray-100 disabled:cursor-not-allowed">
                            </div>
                            @error('barcode')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Row 3: Category & Brand -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category_id"
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Category</label>
                            <select id="category_id" name="category_id" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                <option value="">Select category</option>
                                @foreach ($categories as $id => $name)
                                    <option value="{{ $id }}" @selected(old('category_id', $product->category_id) == $id)>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="brand_id"
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Brand</label>
                            <select id="brand_id" name="brand_id" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                <option value="">Select brand</option>
                                @foreach ($brands as $id => $name)
                                    <option value="{{ $id }}" @selected(old('brand_id', $product->brand_id) == $id)>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Row 4: Unit & Tax -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="unit_id"
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Unit</label>
                            <select id="unit_id" name="unit_id" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                <option value="">Select unit</option>
                                @foreach ($units as $id => $name)
                                    <option value="{{ $id }}" @selected(old('unit_id', $product->unit_id) == $id)>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="tax_id"
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Tax
                                (Optional)</label>
                            <select id="tax_id" name="tax_id"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                <option value="">No tax</option>
                                @foreach ($taxes as $id => $name)
                                    <option value="{{ $id }}" @selected(old('tax_id', $product->tax_id) == $id)>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tax_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Row 5: Prices -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sale_price"
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Sale
                                Price</label>
                            <input type="number" id="sale_price" name="sale_price"
                                value="{{ old('sale_price', $product->sale_price) }}" required min="0"
                                step="1" autocomplete="off"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @error('sale_price')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Last
                                Purchase Price</label>
                            <div class="px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-gray-600">
                                {{ number_format($product->latest_purchase_price ?? 0) }}
                                <span class="text-xs text-gray-400 ml-1">(from FIFO)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Description</label>
                        <input id="description" type="hidden" name="description"
                            value="{{ old('description', $product->description) }}">
                        <trix-editor input="description"
                            class="rounded-lg border border-gray-200 bg-white min-h-[100px]"></trix-editor>
                        @error('description')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                    <a href="{{ route('products.index') }}"
                        class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-save mr-2"></i>Update Product
                    </button>
                </div>
            </div>

            <!-- Image Card -->
            <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Product Image</h3>

                    <div class="flex flex-col sm:flex-row gap-6">
                        <div class="flex-1">
                            <label for="image"
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Upload
                                Image</label>
                            <input type="file" id="image" name="image" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:cursor-pointer cursor-pointer">
                            <input type="hidden" name="remove_image" id="remove_image" value="0">
                            @error('image')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex-shrink-0">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Current
                                Image</label>
                            @if ($product->image)
                                <div id="imagePreviewContainer" class="relative">
                                    <img id="imagePreview" src="{{ $product->image_url }}" alt="Product"
                                        class="w-28 h-28 object-cover rounded-xl border-2 border-gray-200">
                                    <button type="button" id="removeImageBtn"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">{{ $product->image }}</p>
                            @else
                                <div
                                    class="w-28 h-28 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center">
                                    <span class="text-gray-400 text-sm">No image</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('image');
            const previewContainer = document.getElementById('imagePreviewContainer');
            const previewImage = document.getElementById('imagePreview');
            const removeBtn = document.getElementById('removeImageBtn');
            const removeImageInput = document.getElementById('remove_image');

            if (imageInput && previewImage) {
                imageInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            if (previewContainer) {
                                previewContainer.classList.remove('hidden');
                            }
                            if (removeImageInput) {
                                removeImageInput.value = '0';
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            if (removeBtn && previewImage && previewContainer) {
                removeBtn.addEventListener('click', function() {
                    if (imageInput) imageInput.value = '';
                    previewImage.src = '';
                    previewContainer.classList.add('hidden');
                    if (removeImageInput) {
                        removeImageInput.value = '1';
                    }
                });
            }
        });
    </script>
@endsection
