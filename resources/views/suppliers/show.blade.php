@extends('layouts.app')

@section('title', 'View Supplier')
@section('page-title', 'Supplier Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition">
            <i class="fas fa-arrow-left"></i>
            Back to Suppliers
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('suppliers.edit', $supplier->id) }}" 
                class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition duration-200">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this supplier?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition duration-200">
                    <i class="fas fa-trash mr-2"></i>Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-indigo-600"></i>
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Contact ID</span>
                        <p class="text-gray-800 font-medium">{{ $supplier->contact_id }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Name</span>
                        <p class="text-gray-800 font-medium">{{ $supplier->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Mobile</span>
                        <p class="text-gray-800 font-medium">{{ $supplier->mobile ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Created By</span>
                        <p class="text-gray-800 font-medium">{{ $supplier->creator?->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-indigo-600"></i>
                    Address Information
                </h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500">Address</span>
                        <p class="text-gray-800">{{ $supplier->address ?? 'N/A' }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Township</span>
                            <p class="text-gray-800">{{ $supplier->township ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">City</span>
                            <p class="text-gray-800">{{ $supplier->city ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($supplier->purchases->count() > 0)
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-shopping-bag text-indigo-600"></i>
                    Purchase History
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Purchase ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($supplier->purchases->take(10) as $purchase)
                            <tr>
                                <td class="px-4 py-3 text-sm text-indigo-600 font-medium">#{{ $purchase->id }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        {{ $purchase->status == 'received' ? 'bg-green-100 text-green-800' : 
                                           ($purchase->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-800">{{ number_format($purchase->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $purchase->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($supplier->purchases->count() > 10)
                    <p class="mt-3 text-sm text-gray-500 text-center">Showing 10 of {{ $supplier->purchases->count() }} purchases</p>
                @endif
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-wallet text-indigo-600"></i>
                    Balance Information
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Opening Balance</span>
                        <span class="text-lg font-semibold {{ $supplier->opening_balance > 0 ? 'text-red-600' : 'text-gray-800' }}">
                            {{ number_format($supplier->opening_balance, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Advance Balance</span>
                        <span class="text-lg font-semibold text-green-600">
                            {{ number_format($supplier->advance_balance, 2) }}
                        </span>
                    </div>
                    <hr class="border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Total Purchases</span>
                        <span class="text-sm text-gray-600">{{ $supplier->purchases->count() }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-link text-indigo-600"></i>
                    Social Profile
                </h3>
                @if($supplier->social_profile)
                    <a href="{{ $supplier->social_profile }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 break-all">
                        {{ $supplier->social_profile }}
                    </a>
                @else
                    <p class="text-gray-500">No social profile added</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-indigo-600"></i>
                    Timestamps
                </h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-gray-500">Created:</span>
                        <p class="text-gray-800">{{ $supplier->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Updated:</span>
                        <p class="text-gray-800">{{ $supplier->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
