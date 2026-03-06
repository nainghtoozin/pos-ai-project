<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .pos-product-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
        .pos-product-card.out-of-stock { opacity: 0.5; cursor: not-allowed; }
        .cart-item:hover { background-color: #f9fafb; }
    </style>
</head>
<body class="bg-gray-100 h-screen overflow-hidden" x-data="posApp()">

    <!-- Top Header -->
    <header class="bg-white border-b border-gray-200 px-6 py-3 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <i class="fas fa-cash-register text-indigo-600 text-xl"></i>
                    <span class="text-xl font-bold text-gray-800">POS</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="min-w-[220px]">
                        <label class="block text-xs text-gray-500 mb-1">Branch</label>
                        <select x-model="branchId" @change="loadProducts()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $defaultBranch?->id == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Invoice</label>
                        <span class="font-mono font-bold text-gray-800">{{ $invoiceNo }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="text-xs text-gray-500">Date/Time</div>
                    <div class="font-medium text-gray-800" x-text="currentDateTime"></div>
                </div>
                
                <div class="text-right">
                    <div class="text-xs text-gray-500">Cashier</div>
                    <div class="font-medium text-gray-800">{{ auth()->user()->name }}</div>
                </div>

                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content - Balanced Grid Layout -->
    <main class="grid grid-cols-2 h-[calc(100vh-60px)]">
        <!-- Left Panel - Cart (50%) -->
        <section class="flex flex-col h-full bg-gray-50 border-r border-gray-200">
            <!-- Customer -->
            <div class="p-3 border-b border-gray-200 bg-white flex-shrink-0">
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs text-gray-500">Customer</label>
                    <button @click="openCustomerModal()" type="button" class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-2 py-1 rounded font-medium">
                        <i class="fas fa-plus-circle"></i> New
                    </button>
                </div>
                <select x-model="customerId" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Walk-in Customer</option>
                    <template x-for="customer in customers" :key="customer.id">
                        <option :value="customer.id" x-text="customer.name"></option>
                    </template>
                </select>
            </div>

            <!-- Cart Items Table (scrollable) -->
            <div class="flex-1 overflow-hidden flex flex-col" style="max-height: 45%;">
                <!-- Table Header -->
                <div class="px-3 py-2 bg-gray-100 border-b border-gray-200 flex-shrink-0">
                    <table class="w-full text-xs font-medium text-gray-600">
                        <thead>
                            <tr>
                                <th class="text-left w-2/5">Product</th>
                                <th class="text-center w-1/6">Qty</th>
                                <th class="text-right w-1/6">Price</th>
                                <th class="text-right w-1/6">Subtotal</th>
                                <th class="text-center w-10"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
                
                <!-- Table Body (scrollable) -->
                <div class="flex-1 overflow-y-auto">
                    <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 py-8">
                        <i class="fas fa-shopping-cart text-3xl mb-2"></i>
                        <p class="text-sm">Cart is empty</p>
                    </div>

                    <table x-show="cart.length > 0" class="w-full text-sm border-collapse">
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="(item, index) in cart" :key="index">
                                <tr class="bg-white hover:bg-gray-50">
                                    <td class="px-2 py-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gray-100 rounded flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                <template x-if="item.image">
                                                    <img :src="item.image" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!item.image">
                                                    <i class="fas fa-box text-gray-300 text-xs"></i>
                                                </template>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-medium text-gray-800 truncate" x-text="item.name"></div>
                                                <div class="text-[10px] text-gray-500" x-text="item.sku || 'N/A'"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-1 py-2 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button @click="decrementQty(index)" class="w-5 h-5 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100">
                                                <i class="fas fa-minus text-[8px]"></i>
                                            </button>
                                            <input type="number" x-model.number="item.quantity" @change="validateQty(index)" 
                                                min="1" :max="item.current_stock"
                                                class="w-10 text-center border border-gray-300 rounded py-0.5 text-xs font-medium">
                                            <button @click="incrementQty(index)" class="w-5 h-5 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100">
                                                <i class="fas fa-plus text-[8px]"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-1 py-2 text-right">
                                        <input type="number" x-model.number="item.unit_price" @change="recalculate()" 
                                            min="0" class="w-16 text-right border border-gray-300 rounded px-1 py-0.5 text-xs font-medium">
                                    </td>
                                    <td class="px-2 py-2 text-right font-bold text-gray-800" x-text="formatNumber(itemTotal(index))"></td>
                                    <td class="px-1 py-2 text-center">
                                        <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 p-1">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Section (fixed) -->
            <div class="border-t border-gray-200 bg-white p-3 flex-shrink-0">
                <div class="space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium" x-text="formatNumber(subtotal)">0</span>
                    </div>
                    
                    <!-- Discount / Tax / Shipping Buttons -->
                    <div class="grid grid-cols-3 gap-2">
                        <button @click="showDiscountModal = true" class="flex items-center justify-center gap-1 py-1.5 px-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs font-medium text-gray-700">
                            <i class="fas fa-percent text-gray-400"></i>
                            <span x-text="discount > 0 ? (discountType === 'percentage' ? discount + '%' : formatNumber(discount)) : 'Discount'"></span>
                        </button>
                        <button @click="showTaxModal = true" class="flex items-center justify-center gap-1 py-1.5 px-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs font-medium text-gray-700">
                            <i class="fas fa-file-invoice text-gray-400"></i>
                            <span x-text="selectedTaxName || 'Tax'"></span>
                        </button>
                        <button @click="showShippingModal = true" class="flex items-center justify-center gap-1 py-1.5 px-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs font-medium text-gray-700">
                            <i class="fas fa-truck text-gray-400"></i>
                            <span x-text="shipping > 0 ? formatNumber(shipping) : 'Shipping'"></span>
                        </button>
                    </div>

                    <div class="border-t pt-1 flex justify-between">
                        <span class="font-semibold text-gray-800">Grand Total</span>
                        <span class="text-xl font-bold text-indigo-600" x-text="formatNumber(grandTotal)">0</span>
                    </div>
                </div>

                <!-- Paid Amount -->
                <div class="pt-2 border-t mt-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-600">Paid</span>
                        <input type="number" x-model.number="paidAmount" @input="recalculate()" min="0"
                            class="w-24 text-right border-2 rounded px-2 py-1 font-bold text-sm"
                            :class="dueAmount > 0 ? 'border-yellow-400 bg-yellow-50' : 'border-green-400 bg-green-50'">
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Due</span>
                        <span class="font-bold" :class="dueAmount > 0 ? 'text-red-600' : 'text-green-600'" x-text="formatNumber(dueAmount)">0</span>
                    </div>
                </div>

                <!-- Action Buttons - Always Visible -->
                <div class="grid grid-cols-6 gap-1.5 mt-3">
                    <button @click="setFullPaid()" class="py-1.5 bg-green-500 hover:bg-green-600 text-white font-bold rounded text-[10px]">
                        CASH
                    </button>
                    <button @click="processCredit()" :disabled="!customerId"
                            class="py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded text-[10px] disabled:opacity-50 disabled:cursor-not-allowed">
                        CREDIT
                    </button>
                    <button @click="openMultiplePaymentModal()" class="py-1.5 bg-purple-500 hover:bg-purple-600 text-white font-bold rounded text-[10px]">
                        MULTI
                    </button>
                    <button @click="saveDraft()" class="py-1.5 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded text-[10px]">
                        DRAFT
                    </button>
                    <button @click="suspendSale()" class="py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded text-[10px]">
                        HOLD
                    </button>
                    <button @click="cancelSale()" class="py-1.5 bg-red-500 hover:bg-red-600 text-white font-bold rounded text-[10px]">
                        CANCEL
                    </button>
                </div>
            </div>
        </section>

        <!-- Right Panel - Products (50%) -->
        <section class="flex flex-col bg-white">
            <!-- Filters -->
            <div class="p-3 border-b border-gray-200 flex-shrink-0">
                <div class="grid grid-cols-4 gap-3">
                    <div>
                        <select x-model="categoryId" @change="loadProducts()" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select x-model="brandId" @change="loadProducts()" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="loadProducts()"
                                placeholder="Search products..."
                                class="w-full pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <button @click="scanBarcode()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-barcode text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-3">
                <!-- Loading State -->
                <div x-show="loading" class="flex items-center justify-center h-48">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-indigo-600 text-2xl mb-2"></i>
                        <p class="text-gray-500 text-sm">Loading products...</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && products.length === 0" class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i class="fas fa-box-open text-4xl mb-2"></i>
                    <p class="text-base">No Products Found</p>
                    <p class="text-xs">Try adjusting your search or filters</p>
                </div>

                <!-- Product Grid -->
                <div x-show="!loading && products.length > 0" class="grid grid-cols-4 gap-2">
                    <template x-for="product in products" :key="product.id">
                        <div @click="addToCart(product)" 
                             :class="(product.current_stock || 0) <= 0 ? 'out-of-stock' : ''"
                             class="pos-product-card bg-white border border-gray-200 rounded-lg p-1.5 cursor-pointer hover:border-indigo-300 transition">
                            <div class="h-16 bg-gray-100 rounded mb-1.5 flex items-center justify-center overflow-hidden">
                                <template x-if="product.image">
                                    <img :src="product.image" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!product.image">
                                    <i class="fas fa-box text-gray-300 text-xl"></i>
                                </template>
                            </div>
                            <div class="text-xs font-medium text-gray-800 truncate" x-text="product.name"></div>
                            <div class="flex items-center justify-between mt-1">
                                <div class="text-sm font-bold text-indigo-600" x-text="formatNumber(product.sale_price)"></div>
                                <div class="text-[9px] px-1 py-0.5 rounded-full font-medium"
                                     :class="(product.current_stock || 0) > 10 ? 'bg-green-100 text-green-700' : (product.current_stock || 0) > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'"
                                     x-text="(product.current_stock || 0) + ' ' + (product.unit_short_name || 'pcs')">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>
    </main>

    <!-- Multiple Payment Modal -->
    <div x-show="showMultiplePaymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg z-10">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Multiple Payment</h3>
                    <button @click="showMultiplePaymentModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-4">
                    <div class="space-y-3 mb-4">
                        <template x-for="(payment, index) in splitPayments" :key="index">
                            <div class="flex items-center gap-3">
                                <select x-model="payment.method_id" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" x-model.number="payment.amount" min="0" placeholder="Amount"
                                    class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm text-right">
                                <button @click="removeSplitPayment(index)" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                        <button @click="addSplitPayment()" class="text-indigo-600 hover:text-indigo-800 text-sm">
                            <i class="fas fa-plus"></i> Add Payment Method
                        </button>
                    </div>
                    <div class="border-t pt-3 flex justify-between">
                        <span class="font-medium">Total Paid:</span>
                        <span class="font-bold" :class="splitPaymentsTotal === grandTotal ? 'text-green-600' : 'text-red-600'" x-text="formatNumber(splitPaymentsTotal)"></span>
                    </div>
                </div>
                <div class="p-4 border-t bg-gray-50">
                    <button @click="processMultiplePayment()" 
                            :disabled="splitPaymentsTotal !== grandTotal"
                            class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div x-show="showSuccessModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md z-10 p-6 text-center">
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Sale Completed!</h3>
                <p class="text-gray-600 mb-4">Invoice: <span x-text="lastInvoiceNo" class="font-mono font-bold"></span></p>
                <div class="text-2xl font-bold text-indigo-600 mb-4" x-text="formatNumber(lastGrandTotal)"></div>
                <div class="flex gap-3">
                    <button @click="printReceipt()" class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button @click="newSale()" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg">
                        New Sale
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Customer Modal -->
    <div x-show="showCustomerModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md z-10">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Create New Customer</h3>
                    <button @click="showCustomerModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="createCustomer()">
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input type="text" x-model="newCustomer.name" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Customer name">
                            <p x-show="customerErrors.name" class="text-red-500 text-xs mt-1" x-text="customerErrors.name"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile *</label>
                            <input type="text" x-model="newCustomer.mobile" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Mobile number">
                            <p x-show="customerErrors.mobile" class="text-red-500 text-xs mt-1" x-text="customerErrors.mobile"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" x-model="newCustomer.email"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Email address (optional)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea x-model="newCustomer.address" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Address (optional)"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                            <textarea x-model="newCustomer.note" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Note (optional)"></textarea>
                        </div>
                    </div>
                    <div class="p-4 border-t bg-gray-50 flex gap-3">
                        <button type="button" @click="showCustomerModal = false"
                            class="flex-1 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" :disabled="savingCustomer"
                            class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                            <span x-show="!savingCustomer">Save Customer</span>
                            <span x-show="savingCustomer"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Discount Modal -->
    <div x-show="showDiscountModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="showDiscountModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm z-10">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Apply Discount</h3>
                    <button @click="showDiscountModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="discountType = 'percentage'" 
                                    :class="discountType === 'percentage' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300'"
                                    class="py-3 border-2 rounded-lg font-medium transition">
                                Percentage (%)
                            </button>
                            <button @click="discountType = 'fixed'" 
                                    :class="discountType === 'fixed' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300'"
                                    class="py-3 border-2 rounded-lg font-medium transition">
                                Fixed Amount
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-text="discountType === 'percentage' ? 'Discount Percentage' : 'Discount Amount'"></span>
                        </label>
                        <div class="relative">
                            <input type="number" x-model.number="discount" min="0" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :placeholder="discountType === 'percentage' ? 'Enter %' : 'Enter amount'">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium" x-text="discountType === 'percentage' ? '%' : ''"></span>
                        </div>
                        <p x-show="discountType === 'percentage' && discount > 100" class="text-red-500 text-xs mt-1">
                            Percentage cannot exceed 100%
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Discount Amount:</span>
                            <span class="font-bold text-red-600" x-text="formatNumber(discountAmount)"></span>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t bg-gray-50 flex gap-3">
                    <button @click="discount = 0; showDiscountModal = false" class="flex-1 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100">
                        Clear
                    </button>
                    <button @click="showDiscountModal = false" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tax Modal -->
    <div x-show="showTaxModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="showTaxModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm z-10">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Select Tax</h3>
                    <button @click="showTaxModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-4 space-y-3">
                    <button @click="selectedTaxId = null; tax = 0" 
                            :class="!selectedTaxId ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200'"
                            class="w-full p-3 border-2 rounded-lg text-left hover:border-indigo-300 transition">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">No Tax</span>
                            <span x-show="!selectedTaxId" class="text-indigo-600"><i class="fas fa-check"></i></span>
                        </div>
                    </button>
                    <template x-for="taxItem in taxes" :key="taxItem.id">
                        <button @click="applyTax(taxItem)" 
                                :class="selectedTaxId === taxItem.id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200'"
                                class="w-full p-3 border-2 rounded-lg text-left hover:border-indigo-300 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium" x-text="taxItem.name"></div>
                                    <div class="text-xs text-gray-500" x-text="taxItem.percentage + '%'"></div>
                                </div>
                                <span x-show="selectedTaxId === taxItem.id" class="text-indigo-600"><i class="fas fa-check"></i></span>
                            </div>
                        </button>
                    </template>
                </div>
                <div class="p-4 border-t bg-gray-50">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax Amount:</span>
                        <span class="font-bold text-orange-600" x-text="formatNumber(taxAmount)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping Modal -->
    <div x-show="showShippingModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="showShippingModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm z-10">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Shipping Cost</h3>
                    <button @click="showShippingModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Charge</label>
                        <div class="relative">
                            <input type="number" x-model.number="shipping" min="0" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Enter shipping cost">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">MMK</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping Cost:</span>
                            <span class="font-bold text-blue-600" x-text="formatNumber(shipping || 0)"></span>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t bg-gray-50 flex gap-3">
                    <button @click="shipping = 0; showShippingModal = false" class="flex-1 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100">
                        Clear
                    </button>
                    <button @click="showShippingModal = false" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function posApp() {
            return {
                branchId: {{ $defaultBranch?->id ?? 1 }},
                customerId: '',
                customers: [],
                categoryId: '',
                brandId: '',
                searchQuery: '',
                products: [],
                cart: [],
                loading: false,
                currentDateTime: '',
                
                discount: 0,
                discountType: 'percentage',
                tax: 0,
                taxType: 'percentage',
                shipping: 0,
                paidAmount: 0,
                
                selectedTaxId: null,
                selectedTaxName: null,
                taxes: [],
                
                showDiscountModal: false,
                showTaxModal: false,
                showShippingModal: false,
                showMultiplePaymentModal: false,
                showSuccessModal: false,
                showCustomerModal: false,
                splitPayments: [],
                savingCustomer: false,
                customerErrors: {},
                newCustomer: {
                    name: '',
                    mobile: '',
                    email: '',
                    address: '',
                    note: ''
                },
                
                lastInvoiceNo: '',
                lastGrandTotal: 0,

                init() {
                    this.updateDateTime();
                    setInterval(() => this.updateDateTime(), 1000);
                    this.loadProducts();
                    this.loadCustomers();
                    this.loadTaxes();
                },

                async loadTaxes() {
                    try {
                        const response = await fetch('/api/taxes/list');
                        const contentType = response.headers.get("content-type");
                        if (!contentType || !contentType.includes("application/json")) {
                            throw new Error("Invalid JSON response");
                        }
                        if (!response.ok) throw new Error('Network error');
                        const data = await response.json();
                        this.taxes = data.data || [];
                    } catch (error) {
                        console.error('Error loading taxes:', error);
                        this.taxes = [];
                    }
                },

                applyTax(taxItem) {
                    this.selectedTaxId = taxItem.id;
                    this.selectedTaxName = taxItem.name;
                    this.tax = taxItem.percentage;
                    this.taxType = 'percentage';
                    this.showTaxModal = false;
                    this.recalculate();
                },

                async loadCustomers() {
                    try {
                        const response = await fetch('/api/customers/list');
                        const contentType = response.headers.get("content-type");
                        if (!contentType || !contentType.includes("application/json")) {
                            throw new Error("Invalid JSON response");
                        }
                        if (!response.ok) throw new Error('Network error');
                        const data = await response.json();
                        this.customers = data.data || [];
                    } catch (error) {
                        console.error('Error loading customers:', error);
                        this.customers = [];
                    }
                },

                openCustomerModal() {
                    this.newCustomer = { name: '', mobile: '', email: '', address: '', note: '' };
                    this.customerErrors = {};
                    this.showCustomerModal = true;
                },

                async createCustomer() {
                    this.savingCustomer = true;
                    this.customerErrors = {};
                    
                    try {
                        const response = await fetch('/customers/quick-store', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.newCustomer)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            this.customers.push(result.customer);
                            this.customerId = result.customer.id;
                            this.showCustomerModal = false;
                            alert('Customer created successfully!');
                        } else {
                            if (result.errors) {
                                this.customerErrors = result.errors;
                            } else {
                                alert(result.message || 'Error creating customer');
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error creating customer');
                    }
                    
                    this.savingCustomer = false;
                },

                updateDateTime() {
                    const now = new Date();
                    this.currentDateTime = now.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                async loadProducts() {
                    this.loading = true;
                    try {
                        let url = `/products/branch/${this.branchId}/search?`;
                        if (this.categoryId) url += `category_id=${this.categoryId}&`;
                        if (this.brandId) url += `brand_id=${this.brandId}&`;
                        if (this.searchQuery) url += `q=${encodeURIComponent(this.searchQuery)}&`;
                        
                        console.log('Loading products from:', url);
                        
                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Network error');
                        
                        const data = await response.json();
                        console.log('Products loaded:', data.length);
                        
                        this.products = Array.isArray(data) ? data : [];
                        console.log('Products in state:', this.products.length);
                    } catch (error) {
                        console.error('Error loading products:', error);
                        this.products = [];
                    }
                    this.loading = false;
                },

                addToCart(product) {
                    if (!product || (product.current_stock || 0) <= 0) return;
                    
                    const existing = this.cart.find(item => item.product_id === product.id);
                    if (existing) {
                        if (existing.quantity < (product.current_stock || 0)) {
                            existing.quantity++;
                        }
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            name: product.name || 'Unknown',
                            sku: product.sku || '',
                            image: product.image || null,
                            unit_price: product.sale_price || 0,
                            current_stock: product.current_stock || 0,
                            quantity: 1
                        });
                    }
                    this.recalculate();
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                    this.recalculate();
                },

                incrementQty(index) {
                    const item = this.cart[index];
                    if (item.quantity < item.current_stock) {
                        item.quantity++;
                        this.recalculate();
                    }
                },

                decrementQty(index) {
                    const item = this.cart[index];
                    if (item.quantity > 1) {
                        item.quantity--;
                        this.recalculate();
                    }
                },

                validateQty(index) {
                    const item = this.cart[index];
                    if (item.quantity < 1) item.quantity = 1;
                    if (item.quantity > item.current_stock) item.quantity = item.current_stock;
                    this.recalculate();
                },

                itemTotal(index) {
                    const item = this.cart[index];
                    if (!item) return 0;
                    return (item.quantity || 0) * (item.unit_price || 0);
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + this.itemTotal(this.cart.indexOf(item)), 0);
                },

                get discountAmount() {
                    if (this.discountType === 'percentage') {
                        return Math.round(this.subtotal * this.discount / 100);
                    }
                    return this.discount || 0;
                },

                get taxAmount() {
                    if (this.taxType === 'percentage') {
                        return Math.round(this.subtotal * this.tax / 100);
                    }
                    return this.tax || 0;
                },

                get grandTotal() {
                    return this.subtotal - this.discountAmount + this.taxAmount + (this.shipping || 0);
                },

                get dueAmount() {
                    return Math.max(0, this.grandTotal - (this.paidAmount || 0));
                },

                get splitPaymentsTotal() {
                    return this.splitPayments.reduce((sum, p) => sum + (p.amount || 0), 0);
                },

                recalculate() {
                    this.cart = [...this.cart];
                },

                formatNumber(num) {
                    return parseInt(num || 0).toLocaleString();
                },

                setFullPaid() {
                    this.paidAmount = this.grandTotal;
                    this.processSale('completed');
                },

                processCredit() {
                    if (!this.customerId) {
                        alert('Please select a customer for credit sale');
                        return;
                    }
                    this.paidAmount = 0;
                    this.processSale('completed');
                },

                openMultiplePaymentModal() {
                    this.splitPayments = [{ method_id: '', amount: 0 }];
                    this.showMultiplePaymentModal = true;
                },

                addSplitPayment() {
                    this.splitPayments.push({ method_id: '', amount: 0 });
                },

                removeSplitPayment(index) {
                    this.splitPayments.splice(index, 1);
                },

                processMultiplePayment() {
                    if (this.splitPaymentsTotal !== this.grandTotal) {
                        alert('Total payment must equal grand total');
                        return;
                    }
                    this.processSale('completed', true);
                    this.showMultiplePaymentModal = false;
                },

                saveDraft() {
                    this.processSale('draft');
                },

                suspendSale() {
                    this.processSale('suspended');
                },

                cancelSale() {
                    if (this.cart.length > 0 && !confirm('Are you sure you want to cancel this sale?')) {
                        return;
                    }
                    this.resetCart();
                },

                async processSale(status, multiplePayment = false) {
                    if (this.cart.length === 0) {
                        alert('Please add products to cart');
                        return;
                    }

                    const data = {
                        branch_id: this.branchId,
                        customer_id: this.customerId || null,
                        items: this.cart.map(item => ({
                            product_id: item.product_id,
                            quantity: item.quantity,
                            unit_price: item.unit_price
                        })),
                        discount: this.discount,
                        discount_type: this.discountType,
                        tax: this.tax,
                        tax_type: this.taxType,
                        shipping: this.shipping,
                        paid_amount: this.paidAmount,
                        status: status
                    };

                    if (multiplePayment) {
                        data.payments = this.splitPayments.map(p => ({
                            payment_method_id: p.method_id,
                            amount: p.amount
                        }));
                    }

                    try {
                        let endpoint = '/sales';
                        if (status === 'draft') endpoint = '/sales/draft';
                        else if (status === 'suspended') endpoint = '/sales/suspend';
                        else if (multiplePayment) endpoint = '/sales/multiple-payment';

                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(data)
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.lastInvoiceNo = result.data.invoice_no;
                            this.lastGrandTotal = result.data.grand_total;
                            this.showSuccessModal = true;
                        } else {
                            alert(result.message || 'Error processing sale');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error processing sale');
                    }
                },

                printReceipt() {
                    alert('Printing receipt: ' + this.lastInvoiceNo);
                },

                newSale() {
                    this.showSuccessModal = false;
                    this.resetCart();
                    window.location.reload();
                },

                resetCart() {
                    this.cart = [];
                    this.customerId = '';
                    this.discount = 0;
                    this.discountType = 'percentage';
                    this.tax = 0;
                    this.taxType = 'percentage';
                    this.selectedTaxId = null;
                    this.selectedTaxName = null;
                    this.shipping = 0;
                    this.paidAmount = 0;
                    this.recalculate();
                },

                scanBarcode() {
                    const barcode = prompt('Enter barcode:');
                    if (barcode) {
                        this.searchQuery = barcode;
                        this.loadProducts();
                    }
                }
            };
        }
    </script>
</body>
</html>
