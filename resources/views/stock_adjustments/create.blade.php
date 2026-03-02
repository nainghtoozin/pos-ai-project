@extends('layouts.app')

@section('title', 'Add Stock Adjustment')
@section('page-title', 'Add Stock Adjustment')

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
    <form id="adjustmentForm" method="POST" action="{{ route('stock_adjustments.store') }}">
        @csrf

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center gap-2 text-red-700 font-medium mb-2">
                    <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
                </div>
                <ul class="list-disc list-inside text-sm text-red-600">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center gap-2 text-red-700 font-medium">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            </div>
        @endif

        <div x-data="adjustmentForm()" class="space-y-6">
            <!-- Confirmation Modal -->
            <div x-show="showConfirmModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showConfirmModal = false"></div>
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md z-10 p-6">
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-check text-indigo-600 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Stock Adjustment</h3>
                            <p class="text-gray-600 mb-6">Are you sure you want to save this stock adjustment?</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showConfirmModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">Cancel</button>
                            <button type="button" @click="submitAdjustment()"
                                class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference No</label>
                        <input type="text" value="{{ $referenceNo }}" readonly
                            class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Date</label>
                        <input type="date" name="adjustment_date" x-model="adjustmentDate" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Type</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="increase" x-model="type" @change="typeChanged()"
                                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700">
                                    <i class="fas fa-plus-circle text-green-500"></i> Increase
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="decrease" x-model="type" @change="typeChanged()"
                                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700">
                                    <i class="fas fa-minus-circle text-red-500"></i> Decrease
                                </span>
                            </label>
                        </div>
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
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                        @keydown.arrow-down.prevent="navigateDown()" @keydown.arrow-up.prevent="navigateUp()"
                        @keydown.enter.prevent="selectHighlighted()" @keydown.escape="closeDropdown()"
                        placeholder="Search by name, SKU, or barcode..."
                        class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        autocomplete="off">

                    <!-- Dropdown - using x-show instead of x-if -->
                    <div x-show="isDropdownOpen()" x-transition
                        class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-y-auto">
                        <div x-show="loading" class="p-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
                        </div>

                        <div x-show="!loading && searchResults.length === 0 && searchQuery.length >= 2"
                            class="p-4 text-center text-gray-500">
                            No products found
                        </div>

                        <template x-for="(product, index) in searchResults" :key="product.id">
                            <div @click="addProduct(product)" @mouseenter="highlightedIndex = index"
                                :class="highlightedIndex === index ? 'bg-indigo-50 border-l-4 border-indigo-500' :
                                    'border-l-4 border-transparent'"
                                class="p-3 cursor-pointer border-b border-gray-100 last:border-0 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-800" x-text="product.name"></div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <template x-if="product.sku">
                                                <span>SKU: <span x-text="product.sku"></span></span>
                                            </template>
                                            <span class="ml-2 text-green-600 font-medium">Stock: <span
                                                    x-text="product.current_stock || 0"></span> pcs</span>
                                        </div>
                                    </div>
                                    <div class="text-right ml-4">
                                        <div class="text-sm font-bold text-indigo-600">Avg Cost: <span
                                                x-text="formatNumber(product.avg_cost)"></span></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-list text-indigo-600"></i>
                        Adjustment Items
                        <span x-show="items.length > 0"
                            class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full"
                            x-text="items.length"></span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full" x-show="items.length > 0">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-24">
                                    Current Stock</th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-24">Qty
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-28"
                                    x-show="type === 'increase'">Unit Cost</th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-32">Reason
                                </th>
                                <th class="px-2 py-3 text-right text-xs font-semibold text-gray-600 uppercase w-24">Total
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-14"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(item, index) in items" :key="item.product_id">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-3 py-3">
                                        <input type="hidden" :name="`items[${index}][product_id]`"
                                            :value="item.product_id">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-800" x-text="item.name"></span>
                                            <span class="text-xs text-gray-400"
                                                x-text="'(' + (item.sku || 'N/A') + ')'"></span>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <span class="text-sm font-medium"
                                            :class="(item.current_stock || 0) > 0 ? 'text-green-600' : 'text-red-600'"
                                            x-text="item.current_stock || 0"></span>
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" :name="`items[${index}][quantity]`"
                                            x-model.number="item.quantity" @input="calculateTotal(index)" min="1"
                                            class="w-full px-2 py-2 text-center bg-white border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                    </td>
                                    <td class="px-2 py-2" x-show="type === 'increase'">
                                        <input type="number" :name="`items[${index}][unit_cost]`"
                                            x-model.number="item.unit_cost" @input="calculateTotal(index)" min="0"
                                            placeholder="Cost"
                                            class="w-full px-2 py-2 text-center bg-white border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                    </td>
                                    <td class="px-2 py-2">
                                        <select :name="`items[${index}][reason]`" x-model="item.reason"
                                            class="w-full px-2 py-2 text-center bg-white border border-gray-300 rounded text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                            <option value="damage">Damage</option>
                                            <option value="expired">Expired</option>
                                            <option value="lost">Lost</option>
                                            <option value="found">Found</option>
                                            <option value="correction">Correction</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </td>
                                    <td class="px-2 py-3 text-right font-bold text-gray-800"
                                        x-text="formatNumber(itemTotal(index))">
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <button type="button" @click="removeItem(index)"
                                            class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition">
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
                    <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500 font-medium text-lg">No products added</p>
                    <p class="text-gray-400 text-sm mt-1">Search and add products above</p>
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-sticky-note text-indigo-600"></i>
                    Notes
                </h3>
                <textarea name="note" rows="3" x-model="note" placeholder="Add any notes about this adjustment..."
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"></textarea>
            </div>

            <!-- Summary Footer -->
            <div
                class="sticky bottom-0 bg-white border-t border-gray-200 p-4 -mx-4 -mb-6 lg:rounded-xl lg:border lg:shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="bg-indigo-50 px-6 py-3 rounded-lg border border-indigo-200">
                            <span class="text-indigo-600 text-sm block">Total Adjustment Cost</span>
                            <span class="text-xl font-bold text-indigo-700" x-text="formatNumber(totalCost)">0</span>
                        </div>
                        <div class="bg-gray-50 px-6 py-3 rounded-lg border border-gray-200">
                            <span class="text-gray-600 text-sm block">Total Items</span>
                            <span class="text-xl font-bold text-gray-800" x-text="items.length">0</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('stock_adjustments.index') }}"
                            class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button type="button" @click="submitForm()"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition shadow-sm">
                            Save Adjustment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        function adjustmentForm() {
            return {
                // State
                searchQuery: '',
                searchResults: [],
                loading: false,
                highlightedIndex: -1,
                searchTimeout: null,

                type: 'increase',
                adjustmentDate: new Date().toISOString().split('T')[0],
                note: '',

                items: [],

                showConfirmModal: false,

                // Computed
                get totalCost() {
                    return this.items.reduce((sum, item) => {
                        const qty = item.quantity || 0;
                        const cost = item.unit_cost || 0;
                        return sum + (qty * cost);
                    }, 0);
                },

                // Methods
                formatNumber(num) {
                    return parseInt(num || 0).toLocaleString();
                },

                itemTotal(index) {
                    const item = this.items[index];
                    if (!item) return 0;
                    return (item.quantity || 0) * (item.unit_cost || 0);
                },

                calculateTotal(index) {
                    // Force reactivity by reassigning array
                    const newItems = [...this.items];
                    this.items = newItems;
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

                    if (this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }

                    this.searchTimeout = setTimeout(() => {
                        const query = this.searchQuery;
                        fetch(`/products/live-search?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                // Only update if query hasn't changed
                                if (this.searchQuery === query) {
                                    this.searchResults = data || [];
                                    this.highlightedIndex = data && data.length > 0 ? 0 : -1;
                                    this.loading = false;
                                }
                            })
                            .catch(() => {
                                if (this.searchQuery === query) {
                                    this.searchResults = [];
                                    this.loading = false;
                                }
                            });
                    }, 300);
                },

                closeDropdown() {
                    this.searchResults = [];
                    this.highlightedIndex = -1;
                },

                navigateDown() {
                    if (this.searchResults.length === 0) return;
                    if (this.highlightedIndex < this.searchResults.length - 1) {
                        this.highlightedIndex++;
                    }
                },

                navigateUp() {
                    if (this.searchResults.length === 0) return;
                    if (this.highlightedIndex > 0) {
                        this.highlightedIndex--;
                    }
                },

                selectHighlighted() {
                    if (this.highlightedIndex >= 0 && this.highlightedIndex < this.searchResults.length) {
                        this.addProduct(this.searchResults[this.highlightedIndex]);
                    }
                },

                addProduct(product) {
                    // Defensive: ensure product exists
                    if (!product || !product.id) {
                        console.error('Invalid product data');
                        return;
                    }

                    // Check duplicate
                    const exists = this.items.some(item => item.product_id === product.id);
                    if (exists) {
                        alert('Product already added!');
                        return;
                    }

                    // Add with safe defaults
                    this.items.push({
                        product_id: product.id,
                        name: product.name || 'Unknown',
                        sku: product.sku || '',
                        current_stock: parseInt(product.current_stock) || 0,
                        quantity: 1,
                        unit_cost: parseInt(product.avg_cost) || 0,
                        reason: 'correction'
                    });

                    this.closeDropdown();
                    this.searchQuery = '';
                },

                removeItem(index) {
                    if (index >= 0 && index < this.items.length) {
                        this.items.splice(index, 1);
                    }
                },

                typeChanged() {
                    // Reset unit costs when type changes
                    this.items.forEach(item => {
                        if (this.type === 'decrease') {
                            item.unit_cost = 0;
                        }
                    });
                    // Force reactivity
                    this.items = [...this.items];
                },

                submitForm() {
                    console.log('submitForm called');
                    console.log('Items:', this.items.length);
                    console.log('Type:', this.type);
                    
                    if (this.items.length === 0) {
                        alert('Please add at least one product.');
                        return;
                    }

                    if (!this.type) {
                        alert('Please select adjustment type.');
                        return;
                    }

                    // Validate quantities
                    for (let i = 0; i < this.items.length; i++) {
                        const item = this.items[i];

                        if (!item.quantity || item.quantity < 1) {
                            alert('Quantity must be at least 1.');
                            return;
                        }

                        if (this.type === 'decrease' && item.quantity > (item.current_stock || 0)) {
                            alert(`Insufficient stock for ${item.name}. Available: ${item.current_stock}`);
                            return;
                        }

                        if (this.type === 'increase' && (!item.unit_cost || item.unit_cost < 0)) {
                            alert(`Unit cost is required for ${item.name} in increase adjustment.`);
                            return;
                        }
                    }

                    console.log('Validation passed, showing modal');
                    this.showConfirmModal = true;
                },

                submitAdjustment() {
                    console.log('Submitting form...');
                    const form = document.getElementById('adjustmentForm');
                    console.log('Form found:', !!form);
                    form.submit();
                }
            };
        }
    </script>
@endsection
