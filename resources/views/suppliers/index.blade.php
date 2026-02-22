@extends('layouts.app')

@section('title', 'Suppliers')
@section('page-title', 'Supplier Management')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Suppliers</h3>
                        <p class="text-sm text-gray-500">Manage your suppliers</p>
                    </div>
                </div>
                <a href="{{ route('suppliers.create') }}"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition duration-200 transform hover:scale-[1.02] shadow-sm">
                    <i class="fas fa-plus"></i>Add Supplier
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

            @if(session('error'))
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center gap-2 text-red-700">
                        <i class="fas fa-exclamation-circle"></i>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="p-6 border-b border-gray-200">
            <form method="GET" action="{{ route('suppliers.index') }}" class="flex gap-2">
                <div class="relative flex-1 max-w-md">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" 
                        placeholder="Search by name, mobile, contact ID..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                               focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                               hover:border-indigo-400 transition duration-200">
                </div>
                <button type="submit" 
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('suppliers.index') }}" 
                        class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition duration-200">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mobile</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">City</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Opening Balance</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Advance Balance</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suppliers as $supplier)
                    <tr class="even:bg-gray-50 hover:bg-indigo-50 transition duration-150">
                        <td class="px-6 py-4 text-sm font-medium text-indigo-600">
                            {{ $supplier->contact_id }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-800">{{ $supplier->name }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $supplier->mobile ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $supplier->city ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm {{ $supplier->opening_balance > 0 ? 'text-red-600' : 'text-gray-600' }}">
                            {{ number_format($supplier->opening_balance, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-green-600">
                            {{ number_format($supplier->advance_balance, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('suppliers.show', $supplier->id) }}" 
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition duration-200"
                                    title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" 
                                    class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition duration-200"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this supplier?')">
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
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fas fa-truck text-gray-300 text-4xl"></i>
                                <p class="text-gray-500 font-medium">No suppliers found</p>
                                <p class="text-gray-400 text-sm">Click "Add Supplier" to create your first supplier</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
