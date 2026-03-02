@extends('layouts.app')

@section('title', 'Purchase Return')
@section('page-title', 'Purchase Return')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="mb-6">
        <a href="{{ route('purchases.index') }}" class="text-indigo-600 hover:text-indigo-800">
            <i class="fas fa-arrow-left mr-1"></i> Back to Purchases
        </a>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-blue-800">Purchase: {{ $purchase->invoice_no }}</h3>
                <p class="text-sm text-blue-600">Supplier: {{ $purchase->supplier?->name ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-blue-600">Total Amount</p>
                <p class="text-2xl font-bold text-blue-800">{{ number_format($purchase->total_amount) }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('purchases.storeReturn', $purchase->id) }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="return_date" class="block text-sm font-medium text-gray-700 mb-1">Return Date <span class="text-red-500">*</span></label>
                <input type="date" name="return_date" id="return_date" value="{{ old('return_date', date('Y-m-d')) }}" 
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
            </div>
        </div>

        <div class="mb-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Select Items to Return</h4>
            <div class="overflow-x-auto">
                <table class="w-full" id="returnItemsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Purchased Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Already Returned</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Return Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($purchase->lines as $index => $line)
                        @php
                            $purchasedQty = $line->quantity;
                            $alreadyReturned = $purchase->returns->flatten()->filter(function($r) use ($line) {
                                return $r->items->contains('product_id', $line->product_id);
                            })->sum(function($r) use ($line) {
                                return $r->items->where('product_id', $line->product_id)->sum('quantity');
                            });
                            $availableQty = $purchasedQty - $alreadyReturned;
                        @endphp
                        @if($availableQty > 0)
                        <tr>
                            <td class="px-4 py-3">
                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $line->product_id }}">
                                <span class="font-medium text-gray-800">{{ $line->product?->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($purchasedQty, 2) }}</td>
                            <td class="px-4 py-3 text-right text-red-600">{{ number_format($alreadyReturned, 2) }}</td>
                            <td class="px-4 py-3">
                                <input type="number" name="items[{{ $index }}][quantity]" 
                                    min="0.01" max="{{ $availableQty }}" 
                                    step="0.01"
                                    data-index="{{ $index }}"
                                    class="return-qty w-24 px-3 py-2 border border-gray-300 rounded-lg text-right focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    onchange="calculateSubtotal({{ $index }})">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="items[{{ $index }}][return_price]" 
                                    value="{{ $line->purchase_price }}"
                                    step="0.01" min="0"
                                    data-index="{{ $index }}"
                                    class="return-price w-24 px-3 py-2 border border-gray-300 rounded-lg text-right focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    onchange="calculateSubtotal({{ $index }})">
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">
                                <span class="subtotal" id="subtotal_{{ $index }}">0.00</span>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-semibold">Total Return Amount:</td>
                            <td class="px-4 py-3 text-right font-bold text-lg">
                                <span id="totalReturnAmount">0.00</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mb-6">
            <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
            <textarea name="note" id="note" rows="3" 
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                placeholder="Add any notes about this return...">{{ old('note') }}</textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('purchases.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">Process Return</button>
        </div>
    </form>
</div>

<script>
function calculateSubtotal(index) {
    const qty = parseFloat(document.querySelector(`.return-qty[data-index="${index}"]`).value) || 0;
    const price = parseFloat(document.querySelector(`.return-price[data-index="${index}"]`).value) || 0;
    const subtotal = qty * price;
    
    document.getElementById(`subtotal_${index}`).textContent = subtotal.toFixed(2);
    
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal').forEach(el => {
        total += parseFloat(el.textContent) || 0;
    });
    document.getElementById('totalReturnAmount').textContent = total.toFixed(2);
}
</script>
@endsection
