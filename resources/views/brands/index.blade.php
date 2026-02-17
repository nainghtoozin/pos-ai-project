@extends('layouts.app')

@section('title', 'Brands')
@section('page-title', 'Brand Management')

@section('content')
<div x-data="brandModalManager()">
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-2xl font-bold text-gray-800">Brands</h2>
            @can('brand.create')
            <button @click="showCreateModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
                <i class="fas fa-plus"></i>
                Create Brand
            </button>
            @endcan
        </div>

        @if(session('success'))
            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <span class="font-medium text-green-700">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <span class="font-medium text-red-700">{{ session('error') }}</span>
            </div>
        @endif

        <div class="mt-6">
            <form method="GET" action="{{ route('brands.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ $search }}" 
                    class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Search brands...">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($brands as $brand)
                <tr class="hover:bg-indigo-50 transition duration-150">
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-gray-800">{{ $brand->name }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-600">{{ $brand->slug }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-600">{{ $brand->description ? Str::limit($brand->description, 50) : '-' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($brand->status === 'active')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @can('brand.edit')
                            <button @click="editModal({{ json_encode(['id' => $brand->id, 'name' => $brand->name, 'slug' => $brand->slug, 'description' => $brand->description, 'status' => $brand->status]) }})"
                                class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition duration-200"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endcan
                            @can('brand.delete')
                            <button @click="deleteModal({{ json_encode(['id' => $brand->id, 'name' => $brand->name]) }})"
                                class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition duration-200"
                                title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-tags text-gray-300 text-4xl"></i>
                            <p class="text-gray-500 font-medium">No brands found</p>
                            <p class="text-gray-400 text-sm">Try adjusting your search criteria</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($brands->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $brands->links() }}
    </div>
    @endif
</div>

<!-- Create Modal -->
<div x-show="showCreateModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showCreateModal = false"></div>
        <div x-show="showCreateModal" @click.outside="showCreateModal = false" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Create Brand</h3>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('brands.store') }}" id="createForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="create_name" class="block text-sm font-semibold text-gray-700">Brand Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="create_name" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200"
                            placeholder="e.g., Apple" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="create_description" class="block text-sm font-semibold text-gray-700">Description</label>
                        <textarea name="description" id="create_description" rows="3"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200"
                            placeholder="Optional description..."></textarea>
                    </div>
                    <div>
                        <label for="create_status" class="block text-sm font-semibold text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="create_status" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showCreateModal = false" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div x-show="showEditModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showEditModal = false"></div>
        <div x-show="showEditModal" @click.outside="showEditModal = false" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Brand</h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="" id="editForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="slug" id="edit_slug" value="">
                <div class="space-y-4">
                    <div>
                        <label for="edit_name" class="block text-sm font-semibold text-gray-700">Brand Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200"
                            placeholder="e.g., Apple" required>
                    </div>
                    <div>
                        <label for="edit_description" class="block text-sm font-semibold text-gray-700">Description</label>
                        <textarea name="description" id="edit_description" rows="3"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200"
                            placeholder="Optional description..."></textarea>
                    </div>
                    <div>
                        <label for="edit_status" class="block text-sm font-semibold text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="edit_status" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showEditModal = false" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
        <div x-show="showDeleteModal" @click.outside="showDeleteModal = false" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Delete Brand</h3>
                <button @click="showDeleteModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="" id="deleteForm">
                @csrf
                @method('DELETE')
                <div class="py-4">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-center text-gray-600">
                        Are you sure you want to delete <span class="font-semibold text-gray-800" x-text="deleteBrandName"></span>?
                    </p>
                    <p class="text-center text-gray-500 text-sm mt-2">This action cannot be undone.</p>
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button type="button" @click="showDeleteModal = false" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script>
function brandModalManager() {
    return {
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false,
        deleteBrandName: '',

        editModal(brand) {
            document.getElementById('edit_name').value = brand.name;
            document.getElementById('edit_slug').value = brand.slug || '';
            document.getElementById('edit_description').value = brand.description || '';
            document.getElementById('edit_status').value = brand.status;
            document.getElementById('editForm').action = '/brands/' + brand.id;
            this.showEditModal = true;
        },

        deleteModal(brand) {
            this.deleteBrandName = brand.name;
            document.getElementById('deleteForm').action = '/brands/' + brand.id;
            this.showDeleteModal = true;
        }
    }
}
</script>
@endsection
