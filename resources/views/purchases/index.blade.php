@extends('layouts.app')

@section('title', 'Purchases')
@section('page-title', 'Purchase Management')

@section('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection

@section('content')
    <div x-data="purchasePage()">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-bag text-indigo-600"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Purchases</h3>
                            <p class="text-sm text-gray-500">Manage your purchase orders</p>
                        </div>
                    </div>
                    <a href="{{ route('purchases.create') }}"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition duration-200 transform hover:scale-[1.02] shadow-sm">
                        <i class="fas fa-plus"></i>Add Purchase
                    </a>
                </div>

                @if (session('success'))
                    <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center gap-2 text-green-700">
                            <i class="fas fa-check-circle"></i>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center gap-2 text-red-700">
                            <i class="fas fa-exclamation-circle"></i>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Filter Section -->
                <div class="mt-4 bg-gray-50 rounded-lg p-4">
                    <form method="GET" action="{{ route('purchases.index') }}" class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Supplier</label>
                            <select name="supplier_id" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none">
                                <option value="">All Suppliers</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Invoice No</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice..." 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div class="flex-1 min-w-[120px]">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div class="flex-1 min-w-[120px]">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div class="flex-1 min-w-[120px]">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Payment Status</label>
                            <select name="payment_status" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-400 focus:outline-none">
                                <option value="">All Status</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="due" {{ request('payment_status') == 'due' ? 'selected' : '' }}>Due</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                <i class="fas fa-filter mr-1"></i>Filter
                            </button>
                            @if(request()->anyFilled(['supplier_id', 'search', 'date_from', 'date_to', 'payment_status']))
                            <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition">
                                <i class="fas fa-times mr-1"></i>Reset
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Supplier</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Grand Total</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Paid</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Due</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Payment</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Created By</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($purchases as $purchase)
                            <tr class="even:bg-gray-50 hover:bg-indigo-50 transition duration-150">
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $purchase->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="text-sm font-medium text-gray-800">{{ $purchase->supplier?->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'ordered' => 'bg-blue-100 text-blue-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'received' => 'bg-green-100 text-green-800',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full {{ $statusColors[$purchase->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-800">
                                    {{ number_format($purchase->total_amount) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-green-600 font-medium">
                                    {{ number_format($purchase->paid_amount) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-red-600 font-medium">
                                    {{ number_format($purchase->due_amount) }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $paymentColors = [
                                            'due' => 'bg-red-100 text-red-800',
                                            'partial' => 'bg-yellow-100 text-yellow-800',
                                            'paid' => 'bg-green-100 text-green-800',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full {{ $paymentColors[$purchase->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($purchase->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $purchase->creator?->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('purchases.show', $purchase->id) }}"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition duration-200"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('purchases.edit', $purchase->id) }}"
                                            class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition duration-200"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('purchases.return', $purchase->id) }}"
                                            class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition duration-200"
                                            title="Return">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                        @if ($purchase->due_amount > 0)
                                            <button type="button"
                                                @click="openPaymentModal({{ $purchase->id }}, {{ $purchase->due_amount }}, '{{ $purchase->invoice_no }}')"
                                                class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition duration-200"
                                                title="Add Payment">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('purchases.print', $purchase->id) }}"
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition duration-200"
                                            title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <form method="POST" action="{{ route('purchases.destroy', $purchase->id) }}"
                                            class="inline"
                                            onsubmit="return confirm('Are you sure you want to delete this purchase?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition duration-200"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="fas fa-shopping-bag text-gray-300 text-4xl"></i>
                                        <p class="text-gray-500 font-medium">No purchases found</p>
                                        <p class="text-gray-400 text-sm">Click "Add Purchase" to create your first purchase
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($purchases->hasPages())
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>

        <!-- Payment Modal -->
        <div x-show="modalOpen" x-cloak>
            <div class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>
                    <div
                        class="relative inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Add Payment</h3>
                            <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form method="POST" :action="paymentFormUrl">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600">Invoice: <span x-text="invoiceNo"
                                            class="font-semibold"></span></p>
                                    <p class="text-sm text-gray-600">Due Amount: <span x-text="formatNumber(dueAmount)"
                                            class="font-semibold text-red-600"></span></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Payment Date <span
                                            class="text-red-500">*</span></label>
                                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Amount <span
                                            class="text-red-500">*</span></label>
                                    <input type="number" name="amount" id="paymentAmount" step="0.01"
                                        min="0.01"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Payment Method</label>
                                    <select name="payment_method_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                        <option value="">Select Payment Method</option>
                                        @foreach ($paymentMethods as $method)
                                            <option value="{{ $method->id }}" {{ $defaultPaymentMethod && $method->id == $defaultPaymentMethod->id ? 'selected' : '' }}>{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Note</label>
                                    <textarea name="note" rows="2"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                        placeholder="Optional note..."></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" @click="closeModal()"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition">Cancel</button>
                                <button type="submit"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">Add
                                    Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('purchasePage', () => ({
                modalOpen: false,
                paymentFormUrl: '',
                invoiceNo: '',
                dueAmount: 0,
                selectedPurchaseId: null,

                openPaymentModal(purchaseId, dueAmount, invoiceNo) {
                    this.selectedPurchaseId = purchaseId;
                    this.dueAmount = dueAmount;
                    this.invoiceNo = invoiceNo;
                    this.paymentFormUrl = '/purchases/' + purchaseId + '/payment';
                    var el = document.getElementById('paymentAmount');
                    if (el) el.value = dueAmount.toFixed(2);
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                    this.paymentFormUrl = '';
                    this.invoiceNo = '';
                    this.dueAmount = 0;
                    this.selectedPurchaseId = null;
                },

                formatNumber(num) {
                    return parseFloat(num).toFixed(2);
                }
            }));
        });
    </script>
@endsection
