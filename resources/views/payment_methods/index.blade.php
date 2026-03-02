@extends('layouts.app')

@section('title', 'Payment Methods')
@section('page-title', 'Payment Methods')

@section('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
<div x-data="paymentMethodModal()">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Payment Methods</h3>
                        <p class="text-sm text-gray-500">Manage payment methods for sales</p>
                    </div>
                </div>
                @can('payment_method.create')
                    <button @click="showCreateModal = true" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition">
                        <i class="fas fa-plus"></i>Create Payment Method
                    </button>
                @endcan
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-2 text-green-700">
                        <i class="fas fa-check-circle"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center gap-2 text-red-700">
                        <i class="fas fa-exclamation-circle"></i>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <form method="GET" action="{{ route('payment_methods.index') }}" class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search payment methods..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <select name="type" onchange="this.form.submit()" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <option value="">All Types</option>
                    <option value="cash" {{ $type === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank" {{ $type === 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="mobile" {{ $type === 'mobile' ? 'selected' : '' }}>Mobile</option>
                    <option value="other" {{ $type === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Account Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Account Number</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($paymentMethods as $method)
                    <tr class="even:bg-gray-50 hover:bg-indigo-50 transition duration-150">
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-gray-800">{{ $method->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $typeClasses = [
                                    'cash' => 'bg-blue-100 text-blue-700',
                                    'bank' => 'bg-purple-100 text-purple-700',
                                    'mobile' => 'bg-green-100 text-green-700',
                                    'other' => 'bg-gray-100 text-gray-700'
                                ];
                                $typeIcons = [
                                    'cash' => 'fa-money-bill-wave',
                                    'bank' => 'fa-university',
                                    'mobile' => 'fa-mobile-alt',
                                    'other' => 'fa-wallet'
                                ];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full {{ $typeClasses[$method->type] }}">
                                <i class="fas {{ $typeIcons[$method->type] }}"></i>
                                {{ ucfirst($method->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $method->account_name ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $method->account_number ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($method->is_default)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-full">
                                    <i class="fas fa-star"></i>System Default
                                </span>
                            @elseif($method->is_system)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                    <i class="fas fa-cog"></i>System
                                </span>
                            @elseif($method->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    <i class="fas fa-check-circle"></i>Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                                    <i class="fas fa-times-circle"></i>Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @if(!$method->is_system)
                                @can('payment_method.edit')
                                <button @click="editModal({{ json_encode(['id' => $method->id, 'name' => $method->name, 'type' => $method->type, 'account_name' => $method->account_name, 'account_number' => $method->account_number, 'is_active' => $method->is_active]) }})" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('payment_method.delete')
                                <button @click="deleteModal({{ json_encode(['id' => $method->id, 'name' => $method->name]) }})" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                                @else
                                <span class="text-xs text-gray-400 italic">System</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fas fa-credit-card text-gray-300 text-4xl"></i>
                                <p class="text-gray-500 font-medium">No payment methods found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paymentMethods->hasPages())
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            {{ $paymentMethods->links() }}
        </div>
        @endif
    </div>

    <!-- Create Modal -->
    <div x-show="showCreateModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
            <div x-show="showCreateModal" @click.outside="closeModals()" class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Create Payment Method</h3>
                    <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
                </div>
                <form method="POST" action="{{ route('payment_methods.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="create_name" class="block text-sm font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="create_name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="e.g., KBZ Pay" required>
                            @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="create_type" class="block text-sm font-semibold text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <select name="type" id="create_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile">Mobile</option>
                                <option value="other">Other</option>
                            </select>
                            @error('type')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="create_account_name" class="block text-sm font-semibold text-gray-700 mb-1">Account Name</label>
                            <input type="text" name="account_name" id="create_account_name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="e.g., John Doe">
                        </div>
                        <div>
                            <label for="create_account_number" class="block text-sm font-semibold text-gray-700 mb-1">Account Number</label>
                            <input type="text" name="account_number" id="create_account_number" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="e.g., 123456789">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                                Active
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeModals()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
            <div x-show="showEditModal" @click.outside="closeModals()" class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Payment Method</h3>
                    <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
                </div>
                <form method="POST" action="" id="editForm">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="edit_name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                            @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="edit_type" class="block text-sm font-semibold text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <select name="type" id="edit_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile">Mobile</option>
                                <option value="other">Other</option>
                            </select>
                            @error('type')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="edit_account_name" class="block text-sm font-semibold text-gray-700 mb-1">Account Name</label>
                            <input type="text" name="account_name" id="edit_account_name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div>
                            <label for="edit_account_number" class="block text-sm font-semibold text-gray-700 mb-1">Account Number</label>
                            <input type="text" name="account_number" id="edit_account_number" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Active
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeModals()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
            <div x-show="showDeleteModal" @click.outside="closeModals()" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Confirm Delete</h3>
                    <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
                </div>
                <div class="py-4">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <p class="text-center text-gray-600">Are you sure you want to delete this payment method?</p>
                    <p class="text-center text-gray-500 text-sm mt-2"><span x-text="deleteName" class="font-semibold"></span></p>
                </div>
                <form method="POST" action="" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeModals()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function paymentMethodModal() {
    return {
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false,
        deleteName: '',

        editModal(method) {
            document.getElementById('edit_name').value = method.name;
            document.getElementById('edit_type').value = method.type;
            document.getElementById('edit_account_name').value = method.account_name || '';
            document.getElementById('edit_account_number').value = method.account_number || '';
            document.getElementById('edit_is_active').checked = method.is_active;
            document.getElementById('editForm').action = '/payment_methods/' + method.id;
            this.showEditModal = true;
        },

        deleteModal(method) {
            this.deleteName = method.name;
            document.getElementById('deleteForm').action = '/payment_methods/' + method.id;
            this.showDeleteModal = true;
        },

        closeModals() {
            this.showCreateModal = false;
            this.showEditModal = false;
            this.showDeleteModal = false;
        }
    }
}
</script>
@endsection
