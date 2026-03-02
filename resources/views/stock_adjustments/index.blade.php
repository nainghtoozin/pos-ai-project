@extends('layouts.app')

@section('title', 'Stock Adjustments')
@section('page-title', 'Stock Adjustments')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sliders-h text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Stock Adjustments</h3>
                    <p class="text-sm text-gray-500">Manage inventory adjustments</p>
                </div>
            </div>
            <a href="{{ route('stock_adjustments.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition">
                <i class="fas fa-plus"></i> New Adjustment
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('stock_adjustments.index') }}" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by reference..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div>
            <select name="type" onchange="this.form.submit()" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                <option value="">All Types</option>
                <option value="increase" {{ $type === 'increase' ? 'selected' : '' }}>Increase</option>
                <option value="decrease" {{ $type === 'decrease' ? 'selected' : '' }}>Decrease</option>
            </select>
            <input type="date" name="date_from" value="{{ $dateFrom }}" placeholder="From Date" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            <input type="date" name="date_to" value="{{ $dateTo }}" placeholder="To Date" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">Filter</button>
        </form>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center gap-2 text-green-700">
                <i class="fas fa-check-circle"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2 text-red-700">
                <i class="fas fa-exclamation-circle"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Reference</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Items</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Total Cost</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Created By</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($adjustments as $adjustment)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-gray-800">{{ $adjustment->reference_no }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $adjustment->adjustment_date->format('d M Y') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($adjustment->type === 'increase')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    <i class="fas fa-plus"></i> Increase
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                    <i class="fas fa-minus"></i> Decrease
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $adjustment->items->count() }} item(s)</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-semibold text-gray-800">
                                {{ number_format($adjustment->items->sum(fn($i) => $i->quantity * ($i->unit_cost ?? 0))) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $adjustment->creator->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('stock_adjustments.show', $adjustment->id) }}" class="text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 p-2 rounded-lg transition" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fas fa-sliders-h text-gray-300 text-4xl"></i>
                                <p class="text-gray-500 font-medium">No stock adjustments found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($adjustments->hasPages())
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            {{ $adjustments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
