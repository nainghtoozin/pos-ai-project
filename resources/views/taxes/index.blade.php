@extends('layouts.app')

@section('title', 'Tax Management')
@section('page-title', 'Tax Management')

@section('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
<div x-data="taxModal()">
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percent text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Tax</h3>
                        <p class="text-sm text-gray-500">Manage tax rates</p>
                    </div>
                </div>
                @can('tax.create')
                    <button @click="showCreateModal = true" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition">
                        <i class="fas fa-plus"></i>Create Tax
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

            <div class="max-w-md">
                <form method="GET" action="{{ route('taxes.index') }}" class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search taxes..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">Search</button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Percentage</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($taxes as $tax)
                    <tr class="even:bg-gray-50 hover:bg-indigo-50 transition duration-150">
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-gray-800">{{ $tax->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-700">{{ number_format($tax->percentage, 2) }}%</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($tax->is_active)
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
                                @can('tax.edit')
                                <button @click="editModal({{ json_encode(['id' => $tax->id, 'name' => $tax->name, 'percentage' => $tax->percentage, 'is_active' => $tax->is_active]) }})" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('tax.delete')
                                <button @click="deleteModal({{ json_encode(['id' => $tax->id, 'name' => $tax->name]) }})" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fas fa-percent text-gray-300 text-4xl"></i>
                                <p class="text-gray-500 font-medium">No taxes found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($taxes->hasPages())
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            {{ $taxes->links() }}
        </div>
        @endif
    </div>

    <!-- Create Modal -->
    <div x-show="showCreateModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
            <div x-show="showCreateModal" @click.outside="closeModals()" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Create New Tax</h3>
                    <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
                </div>
                <form method="POST" action="{{ route('taxes.store') }}" id="createForm">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="create_name" class="block text-sm font-semibold text-gray-700">Tax Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="create_name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="e.g., VAT" required>
                            @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="create_percentage" class="block text-sm font-semibold text-gray-700">Percentage <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="percentage" id="create_percentage" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 pr-8 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="e.g., 10" required>
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">%</span>
                            </div>
                            @error('percentage')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
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
            <div x-show="showEditModal" @click.outside="closeModals()" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Tax</h3>
                    <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
                </div>
                <form method="POST" action="" id="editForm">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-semibold text-gray-700">Tax Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="edit_name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                            @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="edit_percentage" class="block text-sm font-semibold text-gray-700">Percentage <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="percentage" id="edit_percentage" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 pr-8 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">%</span>
                            </div>
                            @error('percentage')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
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
                    <p class="text-center text-gray-600">Are you sure you want to delete this tax?</p>
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
function taxModal() {
    return {
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false,
        deleteName: '',

        editModal(tax) {
            document.getElementById('edit_name').value = tax.name;
            document.getElementById('edit_percentage').value = tax.percentage;
            document.getElementById('edit_is_active').checked = tax.is_active;
            document.getElementById('editForm').action = '/taxes/' + tax.id;
            this.showEditModal = true;
        },

        deleteModal(tax) {
            this.deleteName = tax.name;
            document.getElementById('deleteForm').action = '/taxes/' + tax.id;
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
