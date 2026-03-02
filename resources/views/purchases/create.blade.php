@extends('layouts.app')

@section('title', 'Add Purchase')
@section('page-title', 'Add Purchase')

@section('styles')
<style>
    .product-search-dropdown { max-height: 320px; overflow-y: auto; }
    .product-search-dropdown::-webkit-scrollbar { width: 6px; }
    .product-search-dropdown::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    .purchase-line input { transition: all 0.2s ease; }
    .purchase-line input:focus { transform: scale(1.02); }
</style>
@endsection

@section('content')
<form id="purchaseForm" method="POST" action="{{ route('purchases.store') }}">
    @csrf

    <div x-data="purchaseForm()" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-indigo-600"></i>
                Basic Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                    <div class="flex gap-2">
                        <select name="supplier_id" id="supplier_id" class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->contact_id }})
                                </option>
                            @endforeach
                        </select>
                        <button type="button" onclick="document.getElementById('supplierModal').classList.remove('hidden')" 
                            class="px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition duration-200 flex items-center gap-2" title="Add New Supplier">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Purchase Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ old('status', $purchase->exists ? $purchase->status : 'ordered') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Product Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-box text-indigo-600"></i>
                Add Products
            </h3>
            
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input 
                    type="text" 
                    x-model="searchQuery"
                    @input.debounce.300ms="searchProducts()"
                    @keydown.arrow-down.prevent="navigateDown()"
                    @keydown.arrow-up.prevent="navigateUp()"
                    @keydown.enter.prevent="selectHighlighted()"
                    @keydown.escape="closeDropdown()"
                    @keydown.tab="closeDropdown()"
                    placeholder="Search by name, SKU, or barcode..."
                    class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                    autocomplete="off"
                >
                
                <!-- Dropdown -->
                <div x-show="isDropdownOpen()" x-transition class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-y-auto">
                    <template x-if="loading">
                        <div class="p-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
                        </div>
                    </template>
                    
                    <template x-if="!loading && searchResults.length === 0 && searchQuery.length >= 2">
                        <div class="p-4 text-center text-gray-500">No products found</div>
                    </template>
                    
                    <template x-for="(product, index) in searchResults" :key="product.id">
                        <div 
                            @click="addItem(product)"
                            @mouseenter="highlightedIndex = index"
                            :class="highlightedIndex === index ? 'bg-indigo-50 border-l-4 border-indigo-500' : 'border-l-4 border-transparent'"
                            class="p-3 cursor-pointer border-b border-gray-100 last:border-0 transition"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-800" x-text="product.name"></div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <span x-if="product.sku">SKU: <span x-text="product.sku"></span></span>
                                        <span x-if="product.sku && product.barcode"> | </span>
                                        <span x-if="product.barcode">Barcode: <span x-text="product.barcode"></span></span>
                                        <span class="ml-2 text-green-600 font-medium">Stock: <span x-text="product.current_stock || 0"></span> pcs</span>
                                    </div>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="text-sm font-bold text-indigo-600">Sell: <span x-text="formatNumber(product.sale_price)"></span></div>
                                    <div class="text-xs text-gray-500">Cost: <span x-text="formatNumber(product.purchase_price)"></span></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Purchase Items Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list text-indigo-600"></i>
                    Purchase Items
                    <span x-show="items.length > 0" class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full" x-text="items.length"></span>
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full" x-show="items.length > 0">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-widest">Product</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-20">Qty</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-24">Purchase Price</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-24">Selling Price</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-20">Discount</th>
                            <th class="px-2 py-3 text-right text-xs font-semibold text-gray-600 uppercase w-24">Line Total</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-14"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="item.product_id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-3 py-3">
                                    <input type="hidden" :name="`lines[${index}][product_id]`" :value="item.product_id">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-800" x-text="item.name"></span>
                                        <span class="text-xs text-gray-400" x-text="`(${item.sku})`"></span>
                                    </div>
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" 
                                        :name="`lines[${index}][quantity]`"
                                        x-model.number="item.quantity"
                                        @input="calculateLineTotal(index)"
                                        min="1" step="1"
                                        class="w-full px-2 py-2 text-center bg-white border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none font-medium">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" 
                                        :name="`lines[${index}][purchase_price]`"
                                        x-model.number="item.purchase_price"
                                        @input="calculateLineTotal(index)"
                                        min="0" step="1"
                                        class="w-full px-2 py-2 text-center bg-white border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" 
                                        :name="`lines[${index}][selling_price]`"
                                        x-model.number="item.selling_price"
                                        min="0" step="1"
                                        class="w-full px-2 py-2 text-center bg-white border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" 
                                        :name="`lines[${index}][discount_amount]`"
                                        x-model.number="item.discount_amount"
                                        @input="calculateLineTotal(index)"
                                        min="0" step="1"
                                        class="w-full px-2 py-2 text-center bg-white border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                </td>
                                <td class="px-2 py-3 text-right font-bold text-gray-800" x-text="formatNumber(item.line_total)">
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            <!-- Empty State -->
            <div x-show="items.length === 0" class="p-12 text-center">
                <i class="fas fa-shopping-bag text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 font-medium text-lg">No products added yet</p>
                <p class="text-gray-400 text-sm mt-1">Search and add products above</p>
            </div>
        </div>

        <!-- Calculations -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-calculator text-indigo-600"></i>
                Calculations
            </h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-2">Discount Type</label>
                        <select name="discount_type" id="discount_type" x-model="discountType" @change="calculateGrandTotal()" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            <option value="none">None</option>
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div>
                        <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Discount Value</label>
                        <input type="number" name="discount_amount" id="discount_amount" x-model.number="discountValue" @input="calculateGrandTotal()" min="0" step="1"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="tax_amount" class="block text-sm font-medium text-gray-700 mb-2">Tax Amount</label>
                        <input type="number" name="tax_amount" id="tax_amount" x-model.number="taxAmount" @input="calculateGrandTotal()" min="0" step="1"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    <div>
                        <label for="shipping_charges" class="block text-sm font-medium text-gray-700 mb-2">Shipping Charges</label>
                        <input type="number" name="shipping_charges" id="shipping_charges" x-model.number="shippingCharges" @input="calculateGrandTotal()" min="0" step="1"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    <div>
                        <label for="other_charges" class="block text-sm font-medium text-gray-700 mb-2">Other Charges</label>
                        <input type="number" name="other_charges" id="other_charges" x-model.number="otherCharges" @input="calculateGrandTotal()" min="0" step="1"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-money-bill-wave text-indigo-600"></i>
                Payment Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-2">Paid Amount</label>
                    <input type="number" name="paid_amount" id="paid_amount" x-model.number="paidAmount" @input="calculateGrandTotal()" min="0" step="1"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div>
                    <label for="payment_method_id" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select name="payment_method_id" id="payment_method_id" 
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @php
                            $selectedPaymentMethod = old('payment_method_id', $purchase->payment_method_id ?? ($defaultPaymentMethod->id ?? ''));
                        @endphp
                        <option value="">Select Payment Method</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}" {{ $selectedPaymentMethod == $method->id ? 'selected' : '' }}>
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-sticky-note text-indigo-600"></i>
                Notes
            </h3>
            <textarea name="notes" id="notes" rows="3" 
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                placeholder="Add any additional notes...">{{ old('notes', $purchase->notes ?? '') }}</textarea>
        </div>

        <!-- Summary Footer -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 -mx-4 -mb-6 lg:rounded-xl lg:border lg:shadow-lg">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <!-- Totals -->
                <div class="flex flex-wrap gap-3">
                    <div class="bg-gray-50 px-4 py-3 rounded-lg border border-gray-200 min-w-28">
                        <span class="text-gray-500 text-xs block">Subtotal</span>
                        <span class="text-lg font-bold text-gray-800" x-text="formatNumber(subtotal)">0</span>
                    </div>
                    <div class="bg-red-50 px-4 py-3 rounded-lg border border-red-200 min-w-28">
                        <span class="text-red-500 text-xs block">Discount</span>
                        <span class="text-lg font-bold text-red-600" x-text="formatNumber(discountAmount)">0</span>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 rounded-lg border border-gray-200 min-w-28">
                        <span class="text-gray-500 text-xs block">Extra Charges</span>
                        <span class="text-lg font-bold text-gray-800" x-text="formatNumber(extraCharges)">0</span>
                    </div>
                    <div class="bg-indigo-50 px-4 py-3 rounded-lg border border-indigo-200 min-w-32">
                        <span class="text-indigo-600 text-xs block">Grand Total</span>
                        <span class="text-xl font-bold text-indigo-700" x-text="formatNumber(grandTotal)">0</span>
                    </div>
                    <div class="bg-red-50 px-4 py-3 rounded-lg border border-red-200 min-w-24 text-center">
                        <span class="text-red-500 text-xs block">Due</span>
                        <span class="text-lg font-bold text-red-600" x-text="formatNumber(dueAmount)">0</span>
                    </div>
                    <div class="bg-green-50 px-4 py-3 rounded-lg border border-green-200 min-w-24 text-center">
                        <span class="text-green-600 text-xs block">Status</span>
                        <span class="text-sm font-bold text-green-700" x-text="paymentStatus">Due</span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-3">
                    <a href="{{ route('purchases.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-200 shadow-sm">
                        {{ $purchase->exists ? 'Update Purchase' : 'Save Purchase' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
function purchaseForm() {
    return {
        // Search state
        searchQuery: '',
        searchResults: [],
        loading: false,
        highlightedIndex: -1,
        searchTimeout: null,
        
        // Items
        items: @json($purchaseItems ?? []),
        
        // Calculation state
        discountType: '{{ old('discount_type', $purchase->discount_type ?? 'none') }}',
        discountValue: {{ old('discount_amount', $purchase->discount_amount ?? 0) }},
        taxAmount: {{ old('tax_amount', $purchase->tax_amount ?? 0) }},
        shippingCharges: {{ old('shipping_charges', $purchase->shipping_charges ?? 0) }},
        otherCharges: {{ old('other_charges', $purchase->other_charges ?? 0) }},
        paidAmount: {{ old('paid_amount', $purchase->paid_amount ?? 0) }},
        
        // Computed
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (item.line_total || 0), 0);
        },
        
        get discountAmount() {
            if (this.discountType === 'fixed') return this.discountValue;
            if (this.discountType === 'percentage') return Math.floor(this.subtotal * (this.discountValue / 100));
            return 0;
        },
        
        get extraCharges() {
            return (this.taxAmount || 0) + (this.shippingCharges || 0) + (this.otherCharges || 0);
        },
        
        get grandTotal() {
            return this.subtotal - this.discountAmount + this.extraCharges;
        },
        
        get dueAmount() {
            return this.grandTotal - (this.paidAmount || 0);
        },
        
        get paymentStatus() {
            if (this.paidAmount >= this.grandTotal && this.grandTotal > 0) return 'Paid';
            if (this.paidAmount > 0) return 'Partial';
            return 'Due';
        },
        
        // Methods
        formatNumber(num) {
            return parseInt(num || 0).toLocaleString();
        },
        
        isDropdownOpen() {
            return this.searchResults.length > 0 || (this.loading && this.searchQuery.length >= 2);
        },
        
        searchProducts() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                this.highlightedIndex = -1;
                return;
            }
            
            this.loading = true;
            
            if (this.searchTimeout) clearTimeout(this.searchTimeout);
            
            this.searchTimeout = setTimeout(() => {
                fetch(`/products/live-search?q=${encodeURIComponent(this.searchQuery)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.searchResults = data;
                        this.highlightedIndex = data.length > 0 ? 0 : -1;
                        this.loading = false;
                    })
                    .catch(() => {
                        this.searchResults = [];
                        this.loading = false;
                    });
            }, 300);
        },
        
        closeDropdown() {
            this.searchResults = [];
            this.highlightedIndex = -1;
        },
        
        navigateDown() {
            if (this.searchResults.length === 0) return;
            if (this.highlightedIndex < this.searchResults.length - 1) this.highlightedIndex++;
        },
        
        navigateUp() {
            if (this.searchResults.length === 0) return;
            if (this.highlightedIndex > 0) this.highlightedIndex--;
        },
        
        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.searchResults.length) {
                this.addItem(this.searchResults[this.highlightedIndex]);
            }
        },
        
        addItem(product) {
            // Check duplicate
            if (this.items.find(item => item.product_id === product.id)) {
                alert('Product already added!');
                return;
            }
            
            // Get last purchase price
            const purchasePrice = product.last_purchase_price || product.purchase_price || 0;
            
            // Add new item
            this.items.push({
                product_id: product.id,
                name: product.name,
                sku: product.sku || 'N/A',
                quantity: 1,
                purchase_price: purchasePrice,
                selling_price: product.sale_price || 0,
                discount_amount: 0,
                line_total: purchasePrice
            });
            
            this.calculateLineTotal(this.items.length - 1);
            this.closeDropdown();
            this.searchQuery = '';
        },
        
        removeItem(index) {
            this.items.splice(index, 1);
            this.calculateGrandTotal();
        },
        
        calculateLineTotal(index) {
            const item = this.items[index];
            if (item) {
                item.line_total = (item.quantity * item.purchase_price) - (item.discount_amount || 0);
                this.calculateGrandTotal();
            }
        },
        
        calculateGrandTotal() {
            // This triggers Alpine reactivity
            this.items = [...this.items];
        },
        
        init() {
            this.calculateGrandTotal();
        }
    };
}

document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('[name^="lines["]');
    if (items.length === 0) {
        e.preventDefault();
        alert('Please add at least one product to the purchase.');
        return;
    }
});
</script>

<!-- Supplier Modal -->
<div id="supplierModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="document.getElementById('supplierModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg z-10">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Add New Supplier</h3>
                <button type="button" onclick="document.getElementById('supplierModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="supplierForm" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="supplierName" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            placeholder="Supplier name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile <span class="text-red-500">*</span></label>
                        <input type="text" name="mobile" id="supplierMobile" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            placeholder="Mobile number">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" id="supplierAddress" rows="2"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            placeholder="Address"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Township</label>
                            <input type="text" name="township" id="supplierTownship"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                placeholder="Township">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" name="city" id="supplierCity"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                placeholder="City">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('supplierModal').classList.add('hidden')"
                        class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-200">
                        Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('supplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('supplierName').value,
        mobile: document.getElementById('supplierMobile').value,
        address: document.getElementById('supplierAddress').value,
        township: document.getElementById('supplierTownship').value,
        city: document.getElementById('supplierCity').value,
    };

    fetch('{{ route('suppliers.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const newSupplier = data.supplier;
            const select = document.getElementById('supplier_id');
            const option = document.createElement('option');
            option.value = newSupplier.id;
            option.text = `${newSupplier.name} (${newSupplier.contact_id})`;
            option.selected = true;
            select.add(option);
            
            document.getElementById('supplierModal').classList.add('hidden');
            document.getElementById('supplierForm').reset();
            alert('Supplier created successfully!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create supplier. Please try again.');
    });
});
</script>
@endsection
