@php
    $canEdit = auth()->user()?->can('product.edit');
@endphp

<div 
    x-data="{
        open: false, 
        productId: null, 
        productName: '',
        currentStock: 0,
        quantity: '',
        purchasePrice: '',
        loading: false,
        init(product) {
            if (!product) return;
            this.productId = product.id || null;
            this.productName = product.name || '';
            this.currentStock = product.current_stock || 0;
            this.quantity = '';
            this.purchasePrice = '';
        },
        async save() {
            if (this.loading || !this.productId) return;
            
            this.loading = true;
            
            try {
                const response = await fetch(`/products/${this.productId}/opening-stock`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        quantity: parseInt(this.quantity),
                        purchase_price: parseFloat(this.purchasePrice)
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.open = false;
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update stock');
                }
            } catch (error) {
                alert('An error occurred');
            } finally {
                this.loading = false;
            }
        }
    }"
    x-cloak
>
    <div 
        x-show="open" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="open = false"
        @click.self="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
        >
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Add Opening Stock</h3>
                <button @click="open = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="mb-4 text-sm text-gray-600">
                Product: <span class="font-semibold text-gray-900" x-text="productName"></span>
            </p>
            <p class="mb-4 text-sm text-gray-600">
                Current Stock: <span class="font-semibold text-gray-900" x-text="currentStock"></span>
            </p>

            <div class="space-y-4">
                <div>
                    <label for="opening-quantity" class="block text-sm font-semibold text-gray-700">Quantity</label>
                    <input 
                        type="number" 
                        id="opening-quantity"
                        x-model="quantity"
                        min="1"
                        required
                        class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Enter quantity"
                    >
                </div>
                <div>
                    <label for="opening-price" class="block text-sm font-semibold text-gray-700">Purchase Price</label>
                    <input 
                        type="number" 
                        id="opening-price"
                        x-model="purchasePrice"
                        step="0.0001"
                        min="0"
                        required
                        class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Enter purchase price"
                    >
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button 
                    @click="open = false"
                    type="button"
                    class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button 
                    @click="save()"
                    :disabled="loading || !quantity || !purchasePrice"
                    type="button"
                    class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                >
                    <span x-show="!loading">Save</span>
                    <span x-show="loading">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.querySelector('[x-data]');
            if (!modalEl || typeof Alpine === 'undefined') return;
            
            const alpineData = Alpine.$data(modalEl);
            
            window.addEventListener('open-opening-stock', function(event) {
                const product = event.detail;
                if (product && alpineData.init) {
                    alpineData.init(product);
                    alpineData.open = true;
                }
            });
        });
    </script>
</div>
