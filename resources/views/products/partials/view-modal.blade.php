<div x-data="{ open: false, product: {} }"
     x-on:view-product.window="product = $event.detail; open = true"
     x-on:keydown.escape.window="open = false"
     class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 px-4"
     x-cloak
     x-show="open"
     x-transition.opacity>
    <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl" @click.outside="open = false" x-transition.scale>
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900" x-text="product.name ?? ''"></h2>
                <p class="text-sm text-gray-500" x-text="`SKU ${product.sku ?? ''} · Barcode ${product.barcode ?? '-'}`"></p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600" @click="open = false">✕</button>
        </div>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="h-32 w-full overflow-hidden rounded-xl bg-gray-100">
                <template x-if="product.image_url">
                    <img :src="product.image_url" alt="" w-full object-cover class="h-full">
                </template>
                <template x-if="!product.image_url">
                    <div class="flex h-full w-full items-center justify-center text-xs text-gray-400">No Image</div>
                </template>
            </div>
            <div class="space-y-1 text-sm text-gray-600">
                <p><span class="font-semibold text-gray-900">Category:</span> <span x-text="product.category ?? '--'"></span></p>
                <p><span class="font-semibold text-gray-900">Brand:</span> <span x-text="product.brand ?? '--'"></span></p>
                <p><span class="font-semibold text-gray-900">Unit:</span> <span x-text="product.unit ?? '--'"></span></p>
                <p><span class="font-semibold text-gray-900">Sale Price:</span> <span x-text="product.sale_price ? '$' + parseFloat(product.sale_price).toFixed(4) : '--'"></span></p>
                <p><span class="font-semibold text-gray-900">Stock:</span> <span x-text="product.stock ?? '0.00'"></span></p>
                <p><span class="font-semibold text-gray-900">Status:</span>
                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700" x-text="product.status ?? ''"></span>
                </p>
            </div>
        </div>
        <div class="mt-4 max-h-60 overflow-y-auto rounded-xl bg-gray-50 p-4 text-sm text-gray-700" x-html="product.description || '<p>No description provided.</p>'"></div>
    </div>
</div>
