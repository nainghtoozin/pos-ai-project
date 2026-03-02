@extends('layouts.app')

@section('title', 'Stock Adjustment Details')
@section('page-title', 'Stock Adjustment Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('stock_adjustments.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </a>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Stock Adjustment Details</h3>
                    <p class="text-sm text-gray-500">Reference: {{ $stockAdjustment->reference_no }}</p>
                </div>
            </div>
            <div>
                @if($stockAdjustment->type === 'increase')
                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 text-sm font-semibold rounded-lg">
                        <i class="fas fa-plus-circle"></i> Stock Increase
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 text-sm font-semibold rounded-lg">
                        <i class="fas fa-minus-circle"></i> Stock Decrease
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <span class="text-gray-500 text-sm">Reference No</span>
            <p class="text-lg font-bold text-gray-800">{{ $stockAdjustment->reference_no }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <span class="text-gray-500 text-sm">Adjustment Date</span>
            <p class="text-lg font-bold text-gray-800">{{ $stockAdjustment->adjustment_date->format('d M Y') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <span class="text-gray-500 text-sm">Created By</span>
            <p class="text-lg font-bold text-gray-800">{{ $stockAdjustment->creator->name ?? 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <span class="text-gray-500 text-sm">Total Cost</span>
            <p class="text-lg font-bold text-indigo-600">{{ number_format($stockAdjustment->items->sum(fn($i) => $i->quantity * ($i->unit_cost ?? 0))) }}</p>
        </div>
    </div>

    <!-- Notes -->
    @if($stockAdjustment->note)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Notes</h3>
        <p class="text-gray-600">{{ $stockAdjustment->note }}</p>
    </div>
    @endif

    <!-- Items Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Adjustment Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Unit Cost</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Reason</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($stockAdjustment->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-800">{{ $item->product->name }}</span>
                                <span class="text-xs text-gray-400">({{ $item->product->sku }})</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-semibold {{ $stockAdjustment->type === 'increase' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $stockAdjustment->type === 'increase' ? '+' : '-' }}{{ $item->quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm text-gray-600">{{ number_format($item->unit_cost ?? 0) }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full capitalize">
                                {{ $item->reason }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-bold text-gray-800">{{ number_format($item->quantity * ($item->unit_cost ?? 0)) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right font-semibold text-gray-800">Total</td>
                        <td class="px-6 py-4 text-right font-bold text-lg text-indigo-600">
                            {{ number_format($stockAdjustment->items->sum(fn($i) => $i->quantity * ($i->unit_cost ?? 0))) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Back Button -->
    <div class="flex justify-start">
        <a href="{{ route('stock_adjustments.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>
@endsection
