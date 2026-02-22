@extends('layouts.app')

@section('title', 'Add Purchase')
@section('page-title', 'Add Purchase')

@section('styles')
<style>
    .product-search-dropdown {
        max-height: 300px;
        overflow-y: auto;
    }
    .product-search-dropdown::-webkit-scrollbar {
        width: 6px;
    }
    .product-search-dropdown::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
</style>
@endsection

@section('content')
<form id="purchaseForm" method="POST" action="{{ route('purchases.store') }}">
    @csrf

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
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
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition duration-200 flex items-center gap-2" title="Add New Supplier">
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

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-box text-indigo-600"></i>
                Add Products
            </h3>
            
            <!-- Alpine.js Live Search -->
            <div x-data="productSearch()" @click.outside="closeDropdown()" class="relative">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input 
                        type="text" 
                        x-model="query"
                        @input.debounce.300ms="search()"
                        @keydown.arrow-down.prevent="navigateDown()"
                        @keydown.arrow-up.prevent="navigateUp()"
                        @keydown.enter.prevent="selectHighlighted()"
                        @keydown.escape="closeDropdown()"
                        @keydown.tab="closeDropdown()"
                        placeholder="Search by name, SKU, or barcode..."
                        class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        autocomplete="off"
                    >
                </div>
                
                <!-- Dropdown -->
                <div 
                    x-show="isOpen()"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-y-auto"
                    style="display: none;"
                >
                    <template x-if="loading">
                        <div class="p-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
                        </div>
                    </template>
                    
                    <template x-if="!loading && results.length === 0 && query.length >= 2">
                        <div class="p-4 text-center text-gray-500">
                            No products found
                        </div>
                    </template>
                    
                    <template x-for="(product, index) in results" :key="product.id">
                        <div 
                            @click="selectProduct(product)"
                            @mouseenter="highlightedIndex = index"
                            :class="{'bg-indigo-50 border-l-4 border-indigo-500': highlightedIndex === index, 'border-l-4 border-transparent': highlightedIndex !== index}"
                            class="p-3 cursor-pointer border-b border-gray-100 last:border-0 transition"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-800" x-text="product.name"></div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <template x-if="product.sku"><span>SKU: <span x-text="product.sku"></span></span></template>
                                        <template x-if="product.sku && product.barcode"> | </template>
                                        <template x-if="product.barcode"><span>Barcode: <span x-text="product.barcode"></span></span></template>
                                        <span class="ml-2 text-green-600 font-medium">Stock: <span x-text="product.current_stock || 0"></span> pcs</span>
                                    </div>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="text-sm font-bold text-indigo-600" x-text="formatNumber(product.sale_price)"></div>
                                    <div class="text-xs text-gray-500">Cost: <span x-text="formatNumber(product.purchase_price)"></span></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list text-indigo-600"></i>
                    Purchase Items
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full" id="purchaseLinesTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-24">Qty</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-28">Purchase Price</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-28">Selling Price</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-24">Discount</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase w-28">Line Total</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-16"></th>
                        </tr>
                    </thead>
                    <tbody id="purchaseLinesBody" class="divide-y divide-gray-100">
                        @if($purchase->exists && $purchase->lines->count() > 0)
                            @foreach($purchase->lines as $index => $line)
                            <tr class="purchase-line" data-line-index="{{ $index }}">
                                <td class="px-4 py-3">
                                    <input type="hidden" name="lines[{{ $index }}][product_id]" value="{{ $line->product_id }}">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-800">{{ $line->product->name }}</span>
                                        <span class="text-xs text-gray-500">({{ $line->product->sku }})</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="lines[{{ $index }}][quantity]" 
                                        value="{{ $line->quantity }}" 
                                        min="0.01" step="0.01"
                                        class="w-full px-2 py-1.5 text-center bg-gray-50 border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none line-quantity">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="lines[{{ $index }}][purchase_price]" 
                                        value="{{ $line->purchase_price }}" 
                                        min="0" step="0.01"
                                        class="w-full px-2 py-1.5 text-center bg-gray-50 border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none line-purchase-price">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="lines[{{ $index }}][selling_price]" 
                                        value="{{ $line->selling_price }}" 
                                        min="0" step="0.01"
                                        class="w-full px-2 py-1.5 text-center bg-gray-50 border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none line-selling-price">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="lines[{{ $index }}][discount_amount]" 
                                        value="{{ $line->discount_amount }}" 
                                        min="0" step="0.01"
                                        class="w-full px-2 py-1.5 text-center bg-gray-50 border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none line-discount">
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800 line-total">
                                    {{ number_format($line->line_total, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded transition remove-line">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            @if($purchase->exists && $purchase->lines->count() > 0)
            @else
                <div id="emptyLinesMessage" class="p-12 text-center">
                    <i class="fas fa-shopping-bag text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500 font-medium">No products added yet</p>
                    <p class="text-gray-400 text-sm">Search and add products above</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-calculator text-indigo-600"></i>
                Calculations
            </h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-2">Discount Type</label>
                        <select name="discount_type" id="discount_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            <option value="none" {{ old('discount_type', $purchase->discount_type ?? 'none') == 'none' ? 'selected' : '' }}>None</option>
                            <option value="fixed" {{ old('discount_type', $purchase->discount_type ?? 'none') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            <option value="percentage" {{ old('discount_type', $purchase->discount_type ?? 'none') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                        </select>
                    </div>
                    <div>
                        <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Discount Value</label>
                        <input type="number" name="discount_amount" id="discount_amount" 
                            value="{{ old('discount_amount', $purchase->discount_amount ?? 0) }}" 
                            min="0" step="0.01"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="tax_amount" class="block text-sm font-medium text-gray-700 mb-2">Tax Amount</label>
                        <input type="number" name="tax_amount" id="tax_amount" 
                            value="{{ old('tax_amount', $purchase->tax_amount ?? 0) }}" 
                            min="0" step="0.01"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    <div>
                        <label for="shipping_charges" class="block text-sm font-medium text-gray-700 mb-2">Shipping Charges</label>
                        <input type="number" name="shipping_charges" id="shipping_charges" 
                            value="{{ old('shipping_charges', $purchase->shipping_charges ?? 0) }}" 
                            min="0" step="0.01"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    <div>
                        <label for="other_charges" class="block text-sm font-medium text-gray-700 mb-2">Other Charges</label>
                        <input type="number" name="other_charges" id="other_charges" 
                            value="{{ old('other_charges', $purchase->other_charges ?? 0) }}" 
                            min="0" step="0.01"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-money-bill-wave text-indigo-600"></i>
                Payment Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-2">Paid Amount</label>
                    <input type="number" name="paid_amount" id="paid_amount" 
                        value="{{ old('paid_amount', $purchase->paid_amount ?? 0) }}" 
                        min="0" step="0.01"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <input type="text" name="payment_method" id="payment_method" 
                        value="{{ old('payment_method', $purchase->payment_method ?? '') }}"
                        placeholder="e.g., Cash, Bank Transfer, Cheque"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-sticky-note text-indigo-600"></i>
                Notes
            </h3>
            <textarea name="notes" id="notes" rows="3" 
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                placeholder="Add any additional notes...">{{ old('notes', $purchase->notes ?? '') }}</textarea>
        </div>

        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 p-4 -mx-4 -mb-6 lg:rounded-xl lg:border">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-500 block">Subtotal</span>
                        <span class="text-lg font-bold text-gray-800" id="subtotalDisplay">0.00</span>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-500 block">Discount</span>
                        <span class="text-lg font-bold text-red-600" id="discountDisplay">0.00</span>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-500 block">Extra Charges</span>
                        <span class="text-lg font-bold text-gray-800" id="extraChargesDisplay">0.00</span>
                    </div>
                    <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-200">
                        <span class="text-indigo-600 block">Grand Total</span>
                        <span class="text-xl font-bold text-indigo-700" id="grandTotalDisplay">0.00</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-white p-3 rounded-lg border border-gray-200 text-center min-w-24">
                        <span class="text-gray-500 block text-xs">Due</span>
                        <span class="text-lg font-bold text-red-600" id="dueAmountDisplay">0.00</span>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg border border-green-200 text-center min-w-24">
                        <span class="text-green-600 block text-xs">Payment Status</span>
                        <span class="text-sm font-bold text-green-700" id="paymentStatusDisplay">Due</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('purchases.index') }}" 
                        class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-200 shadow-sm">
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
let lineIndex = 0;
let products = {};

function formatNumber(num) {
    return parseFloat(num || 0).toFixed(2);
}

function calculateTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.purchase-line').forEach(function(row) {
        const quantity = parseFloat(row.querySelector('.line-quantity').value) || 0;
        const purchasePrice = parseFloat(row.querySelector('.line-purchase-price').value) || 0;
        const discount = parseFloat(row.querySelector('.line-discount').value) || 0;
        const lineTotal = (quantity * purchasePrice) - discount;
        
        row.querySelector('.line-total').textContent = formatNumber(lineTotal);
        subtotal += lineTotal;
    });

    const discountType = document.getElementById('discount_type').value;
    const discountValue = parseFloat(document.getElementById('discount_amount').value) || 0;
    let discountAmount = 0;
    
    if (discountType === 'fixed') {
        discountAmount = discountValue;
    } else if (discountType === 'percentage') {
        discountAmount = subtotal * (discountValue / 100);
    }

    const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;
    const shippingCharges = parseFloat(document.getElementById('shipping_charges').value) || 0;
    const otherCharges = parseFloat(document.getElementById('other_charges').value) || 0;
    const extraCharges = taxAmount + shippingCharges + otherCharges;
    
    const grandTotal = subtotal - discountAmount + extraCharges;
    const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
    const dueAmount = grandTotal - paidAmount;

    document.getElementById('subtotalDisplay').textContent = formatNumber(subtotal);
    document.getElementById('discountDisplay').textContent = formatNumber(discountAmount);
    document.getElementById('extraChargesDisplay').textContent = formatNumber(extraCharges);
    document.getElementById('grandTotalDisplay').textContent = formatNumber(grandTotal);
    document.getElementById('dueAmountDisplay').textContent = formatNumber(dueAmount);

    let paymentStatus = 'Due';
    if (paidAmount > 0 && dueAmount > 0) {
        paymentStatus = 'Partial';
    } else if (dueAmount <= 0 && paidAmount > 0) {
        paymentStatus = 'Paid';
    }
    document.getElementById('paymentStatusDisplay').textContent = paymentStatus;
}

function addProductToTable(product) {
    if (products[product.id]) {
        alert('Product already added!');
        return;
    }

    products[product.id] = product;

    const tbody = document.getElementById('purchaseLinesBody');
    const emptyMessage = document.getElementById('emptyLinesMessage');
    if (emptyMessage) {
        emptyMessage.remove();
    }

    const tr = document.createElement('tr');
    tr.className = 'purchase-line';
    tr.dataset.lineIndex = lineIndex;
    tr.dataset.productId = product.id;
    tr.innerHTML = `
        <td class="px-4 py-3">
            <input type="hidden" name="lines[${lineIndex}][product_id]" value="${product.id}">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-800">${product.name}</span>
                <span class="text-xs text-gray-500">(${product.sku || 'N/A'})</span>
            </div>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="lines[${lineIndex}][quantity]" 
                value="1" min="0.01" step="0.01"
                class="w-full px-2 py-1.5 text-center bg-gray-50 border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none line-quantity">
        </td>
        <td class="px-4 py-3">
            <input type="number" name="lines[${lineIndex}][purchase_price]" 
                value="${product.purchase_price || 0}" min="0" step="0.01"
                class="w-full px-2 py-1.5 text-center bg-gray-50 border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none line-purchase-price">
        </td>
        <td class="px-4 py-3">
            <input type="number" name="lines[${lineIndex}][selling_price]" 
                value="${product.sale_price || 0}" min="0" step="0.01"
                class="w-full px-2 py-1.5 text-center bg-gray-50 border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none line-selling-price">
        </td>
        <td class="px-4 py-3">
            <input type="number" name="lines[${lineIndex}][discount_amount]" 
                value="0" min="0" step="0.01"
                class="w-full px-2 py-1.5 text-center bg-gray-50 border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none line-discount">
        </td>
        <td class="px-4 py-3 text-right font-semibold text-gray-800 line-total">
            ${formatNumber(product.purchase_price || 0)}
        </td>
        <td class="px-4 py-3 text-center">
            <button type="button" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded transition remove-line">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    lineIndex++;

    tr.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', calculateTotals);
        input.addEventListener('change', calculateTotals);
    });

    tr.querySelector('.remove-line').addEventListener('click', function() {
        delete products[product.id];
        tr.remove();
        
        if (tbody.children.length === 0) {
            const emptyMsg = document.createElement('div');
            emptyMsg.id = 'emptyLinesMessage';
            emptyMsg.className = 'p-12 text-center';
            emptyMsg.innerHTML = `
                <i class="fas fa-shopping-bag text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500 font-medium">No products added yet</p>
                <p class="text-gray-400 text-sm">Search and add products above</p>
            `;
            tbody.parentElement.appendChild(emptyMsg);
        }
        
        calculateTotals();
    });
}

// Alpine.js Product Search Component
function productSearch() {
    return {
        query: '',
        results: [],
        loading: false,
        highlightedIndex: -1,
        searchTimeout: null,
        
        search() {
            if (this.query.length < 2) {
                this.results = [];
                this.highlightedIndex = -1;
                return;
            }
            
            this.loading = true;
            
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            
            this.searchTimeout = setTimeout(() => {
                fetch(`/products/live-search?q=${encodeURIComponent(this.query)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.results = data;
                        this.highlightedIndex = data.length > 0 ? 0 : -1;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        this.results = [];
                        this.loading = false;
                    });
            }, 300);
        },
        
        isOpen() {
            return this.results.length > 0 || (this.loading && this.query.length >= 2);
        },
        
        closeDropdown() {
            this.results = [];
            this.highlightedIndex = -1;
        },
        
        navigateDown() {
            if (this.results.length === 0) return;
            if (this.highlightedIndex < this.results.length - 1) {
                this.highlightedIndex++;
            }
        },
        
        navigateUp() {
            if (this.results.length === 0) return;
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },
        
        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.results.length) {
                this.selectProduct(this.results[this.highlightedIndex]);
            }
        },
        
        selectProduct(product) {
            addProductToTable(product);
            this.query = '';
            this.results = [];
            this.highlightedIndex = -1;
        },
        
        formatNumber(num) {
            return parseFloat(num || 0).toFixed(2);
        }
    };
}

document.getElementById('discount_type').addEventListener('change', calculateTotals);
document.getElementById('discount_amount').addEventListener('input', calculateTotals);
document.getElementById('tax_amount').addEventListener('input', calculateTotals);
document.getElementById('shipping_charges').addEventListener('input', calculateTotals);
document.getElementById('other_charges').addEventListener('input', calculateTotals);
document.getElementById('paid_amount').addEventListener('input', calculateTotals);

document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    const lines = document.querySelectorAll('.purchase-line');
    if (lines.length === 0) {
        e.preventDefault();
        alert('Please add at least one product to the purchase.');
        return;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
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
                        <p id="nameError" class="mt-1 text-sm text-red-500 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile <span class="text-red-500">*</span></label>
                        <input type="text" name="mobile" id="supplierMobile" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            placeholder="Mobile number">
                        <p id="mobileError" class="mt-1 text-sm text-red-500 hidden"></p>
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
        _token: '{{ csrf_token() }}'
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
            option.text = newSupplier.name + ' (' + newSupplier.contact_id + ')';
            option.selected = true;
            select.add(option);
            
            document.getElementById('supplierModal').classList.add('hidden');
            document.getElementById('supplierForm').reset();
            
            // Show success message
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
