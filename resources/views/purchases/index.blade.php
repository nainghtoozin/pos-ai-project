@extends('layouts.app')

@section('title', 'Purchases')
@section('page-title', 'Purchase Management')

@section('content')
<div class="space-y-6">
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

            @if(session('success'))
                <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-2 text-green-700">
                        <i class="fas fa-check-circle"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Grand Total</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Paid</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Due</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created By</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchases as $purchase)
                    <tr class="even:bg-gray-50 hover:bg-indigo-50 transition duration-150">
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $purchase->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-800">{{ $purchase->supplier?->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'ordered' => 'bg-blue-100 text-blue-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'received' => 'bg-green-100 text-green-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full {{ $statusColors[$purchase->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-semibold text-gray-800">
                            {{ number_format($purchase->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-green-600 font-medium">
                            {{ number_format($purchase->paid_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-red-600 font-medium">
                            {{ number_format($purchase->due_amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $paymentColors = [
                                    'due' => 'bg-red-100 text-red-800',
                                    'partial' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full {{ $paymentColors[$purchase->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($purchase->payment_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $purchase->creator?->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
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
                                <form method="POST" action="{{ route('purchases.destroy', $purchase->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this purchase?')">
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
                                <p class="text-gray-400 text-sm">Click "Add Purchase" to create your first purchase</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
