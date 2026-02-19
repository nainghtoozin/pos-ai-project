@extends('layouts.app')

@section('title', 'Stock Details - ' . $product->name)
@section('page-title', 'Stock Details')

@php
$unitShortName = $product->unit->short_name ?? '';
@endphp

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('stocks.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Stock Details</h2>
                <p class="text-sm text-gray-500">View product stock ledger</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                <i class="fas fa-print mr-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Product Info Card -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row gap-6">
            @if($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-24 w-24 object-cover rounded-xl">
            @else
            <div class="h-24 w-24 bg-gray-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-box text-gray-400 text-3xl"></i>
            </div>
            @endif
            <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Product Name</p>
                    <p class="font-semibold text-gray-800">{{ $product->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">SKU</p>
                    <p class="font-medium text-gray-600">{{ $product->sku }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Category</p>
                    <p class="font-medium text-gray-600">{{ $product->category->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Unit</p>
                    <p class="font-medium text-gray-600">{{ $product->unit->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Stock In -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-arrow-down text-emerald-500"></i>
                Stock In
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Opening Stock</span>
                    <span class="font-semibold text-emerald-600">+{{ \App\Helpers\NumberFormatter::format($stockSummary['opening']) }} {{ $unitShortName }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Purchase</span>
                    <span class="font-semibold text-emerald-600">+{{ \App\Helpers\NumberFormatter::format($stockSummary['purchase']) }} {{ $unitShortName }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Sale Return</span>
                    <span class="font-semibold text-emerald-600">+{{ \App\Helpers\NumberFormatter::format($stockSummary['sale_return']) }} {{ $unitShortName }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Transfer In</span>
                    <span class="font-semibold text-emerald-600">+{{ \App\Helpers\NumberFormatter::format($stockSummary['transfer_in']) }} {{ $unitShortName }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Adjustment Increase</span>
                    <span class="font-semibold text-emerald-600">+{{ \App\Helpers\NumberFormatter::format($stockSummary['adjustment_in']) }} {{ $unitShortName }}</span>
                </div>
                <div class="flex justify-between items-center pt-3">
                    <span class="font-semibold text-gray-700">Total Stock In</span>
                    <span class="font-bold text-emerald-600 text-lg">+{{ \App\Helpers\NumberFormatter::format($stockSummary['stock_in']) }} {{ $unitShortName }}</span>
                </div>
            </div>
        </div>

        <!-- Stock Out -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-arrow-up text-rose-500"></i>
                Stock Out
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Sale</span>
                    <span class="font-semibold text-rose-600">-{{ \App\Helpers\NumberFormatter::format($stockSummary['sale']) }} {{ $unitShortName }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Purchase Return</span>
                    <span class="font-semibold text-rose-600">-{{ \App\Helpers\NumberFormatter::format($stockSummary['purchase_return']) }} {{ $unitShortName }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Transfer Out</span>
                    <span class="font-semibold text-rose-600">-{{ \App\Helpers\NumberFormatter::format($stockSummary['transfer_out']) }} {{ $unitShortName }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Adjustment Decrease</span>
                    <span class="font-semibold text-rose-600">-{{ \App\Helpers\NumberFormatter::format($stockSummary['adjustment_out']) }} {{ $unitShortName }}</span>
                </div>
                <div class="flex justify-between items-center pt-3">
                    <span class="font-semibold text-gray-700">Total Stock Out</span>
                    <span class="font-bold text-rose-600 text-lg">-{{ \App\Helpers\NumberFormatter::format($stockSummary['stock_out']) }} {{ $unitShortName }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Stock Balance -->
    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl shadow-lg p-8 text-white">
        <div class="text-center">
            <p class="text-emerald-100 text-sm uppercase tracking-wider mb-2">Current Stock Balance</p>
            <p class="text-5xl font-bold">
                {{ \App\Helpers\NumberFormatter::format($stockSummary['stock_in'] - $stockSummary['stock_out']) }}
                <span class="text-2xl">{{ $unitShortName }}</span>
            </p>
            <p class="text-emerald-100 mt-2">
                Last updated: {{ now()->format('Y-m-d H:i') }}
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
        <form method="GET" action="{{ route('stocks.show', $product) }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Reference No</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reference..."
                    class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                Filter
            </button>
            @if(request()->has('date_from') || request()->has('date_to') || request()->has('search'))
                <a href="{{ route('stocks.show', $product) }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium rounded-lg transition">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Stock Movement Ledger -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Stock Movement Ledger</h3>
            <p class="text-sm text-gray-500">Running balance calculation</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference No</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-emerald-600 uppercase tracking-wider">In Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-rose-600 uppercase tracking-wider">Out Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($movements as $movement)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $movement->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $type = $movement->type ?? 'purchase';
                                $color = \App\Models\StockMovement::getTypeColor($type);
                                $label = $movement->type_label ?? \App\Models\StockMovement::getTypeLabel($type);
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 bg-{{ $color }}-100 text-{{ $color }}-700 text-xs font-medium rounded-full">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $movement->reference_no ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if(isset($movement->in_qty) && $movement->in_qty > 0)
                                <span class="font-semibold text-emerald-600">+{{ \App\Helpers\NumberFormatter::format($movement->in_qty) }}</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if(isset($movement->out_qty) && $movement->out_qty > 0)
                                <span class="font-semibold text-rose-600">-{{ \App\Helpers\NumberFormatter::format($movement->out_qty) }}</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold {{ $movement->running_balance >= 0 ? 'text-gray-800' : 'text-rose-600' }}">
                                {{ \App\Helpers\NumberFormatter::format($movement->running_balance) }} {{ $unitShortName }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $movement->creator->name ?? ($movement->is_purchase ?? false ? 'System' : '-') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fas fa-clipboard-list text-gray-300 text-4xl"></i>
                                <p class="text-gray-500 font-medium">No stock movements found</p>
                                <p class="text-gray-400 text-sm">Stock movements will appear here after purchases or sales</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
