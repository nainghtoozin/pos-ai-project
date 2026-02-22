@extends('layouts.app')

@section('title', 'View Purchase')
@section('page-title', 'Purchase Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition">
            <i class="fas fa-arrow-left"></i>
            Back to Purchases
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-indigo-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Purchase #{{ $purchase->id }}</h3>
                        <p class="text-sm text-gray-500">Created on {{ $purchase->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @php
                        $statusColors = [
                            'ordered' => 'bg-blue-100 text-blue-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'received' => 'bg-green-100 text-green-800',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-full {{ $statusColors[$purchase->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($purchase->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-list text-indigo-600"></i>
                            Purchase Items
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qty</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Purchase Price</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Selling Price</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Discount</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($purchase->lines as $line)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-800">{{ $line->product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $line->product->sku ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $line->quantity }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($line->purchase_price, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($line->selling_price, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-red-600">{{ number_format($line->discount_amount, 2) }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ number_format($line->line_total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($purchase->notes)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                            <i class="fas fa-sticky-note text-indigo-600"></i>
                            Notes
                        </h4>
                        <p class="text-gray-600">{{ $purchase->notes }}</p>
                    </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-info-circle text-indigo-600"></i>
                            Details
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500">Supplier</span>
                                <p class="text-gray-800 font-medium">{{ $purchase->supplier?->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Created By</span>
                                <p class="text-gray-800 font-medium">{{ $purchase->creator?->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Payment Method</span>
                                <p class="text-gray-800 font-medium">{{ $purchase->payment_method ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-calculator text-indigo-600"></i>
                            Calculations
                        </h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="text-gray-800">{{ number_format($purchase->lines->sum('line_total'), 2) }}</span>
                            </div>
                            @if($purchase->discount_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount ({{ $purchase->discount_type }})</span>
                                <span class="text-red-600">-{{ number_format($purchase->discount_amount, 2) }}</span>
                            </div>
                            @endif
                            @if($purchase->tax_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Tax</span>
                                <span class="text-gray-800">{{ number_format($purchase->tax_amount, 2) }}</span>
                            </div>
                            @endif
                            @if($purchase->shipping_charges > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Shipping</span>
                                <span class="text-gray-800">{{ number_format($purchase->shipping_charges, 2) }}</span>
                            </div>
                            @endif
                            @if($purchase->other_charges > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Other Charges</span>
                                <span class="text-gray-800">{{ number_format($purchase->other_charges, 2) }}</span>
                            </div>
                            @endif
                            <div class="border-t border-gray-200 pt-2 mt-2">
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-800">Grand Total</span>
                                    <span class="font-bold text-indigo-600 text-lg">{{ number_format($purchase->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-money-bill-wave text-indigo-600"></i>
                            Payment
                        </h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Paid Amount</span>
                                <span class="text-green-600 font-medium">{{ number_format($purchase->paid_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Due Amount</span>
                                <span class="text-red-600 font-medium">{{ number_format($purchase->due_amount, 2) }}</span>
                            </div>
                            <div class="mt-3">
                                @php
                                    $paymentColors = [
                                        'due' => 'bg-red-100 text-red-800',
                                        'partial' => 'bg-yellow-100 text-yellow-800',
                                        'paid' => 'bg-green-100 text-green-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-full {{ $paymentColors[$purchase->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
