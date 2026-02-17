@extends('layouts.app')

@section('title', 'Category Management')
@section('page-title', 'Category Management')

@section('styles')
<style>
[x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
<div x-data="categoryModalManager()">
<div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tags text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Categories</h3>
                    <p class="text-sm text-gray-500">Manage product categories</p>
                </div>
            </div>
            @can('category.create')
                <button @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition duration-200 transform hover:scale-[1.02] shadow-sm">
                    <i class="fas fa-plus"></i>Create Category
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

        <div class="max-w-md">
            <form method="GET" action="{{ route('categories.index') }}" class="flex gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" 
                        placeholder="Search categories..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                               focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                               hover:border-indigo-400 transition duration-200">
                </div>
                <button type="submit" 
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
                    Search
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category Name</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Parent</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Subcategories</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                <tr class="even:bg-gray-50 hover:bg-indigo-50 transition duration-150">
                    <td class="px-6 py-4">
                        @if($category->image)
                            <img src="{{ asset('categories/' . $category->image) }}" alt="{{ $category->name }}" class="h-12 w-12 object-cover rounded-md">
                        @else
                            <div class="h-12 w-12 bg-gray-200 rounded-md flex items-center justify-center">
                                <i class="fas fa-image text-gray-400"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-gray-800">{{ $category->name }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($category->parent)
                            <span class="text-sm text-gray-600">{{ $category->parent->name }}</span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">
                            <i class="fas fa-layer-group"></i>
                            {{ $category->children_count }} subcategories
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($category->status == 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                <i class="fas fa-check-circle"></i>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                                <i class="fas fa-times-circle"></i>
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            @can('category.edit')
                            <button @click="editModal({{ json_encode(['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug, 'parent_id' => $category->parent_id, 'status' => $category->status, 'image' => $category->image, 'children_count' => $category->children_count]) }})"
                                class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition duration-200"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endcan
                            @can('category.delete')
                            <button @click="deleteModal({{ json_encode(['id' => $category->id, 'name' => $category->name, 'children_count' => $category->children_count]) }})"
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
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-tags text-gray-300 text-4xl"></i>
                            <p class="text-gray-500 font-medium">No categories found</p>
                            <p class="text-gray-400 text-sm">Try adjusting your search criteria</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
    <div class="p-6 border-t border-gray-200 bg-gray-50">
        {{ $categories->links() }}
    </div>
    @endif
</div>

<!-- Create Modal -->
<div x-show="showCreateModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
        <div x-show="showCreateModal" @click.outside="closeModals()" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Create New Category</h3>
                <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('categories.store') }}" id="createForm" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="create_name" class="block text-sm font-semibold text-gray-700">Category Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="create_name" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200"
                            placeholder="e.g., Electronics" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="create_parent_id" class="block text-sm font-semibold text-gray-700">Parent Category</label>
                        <select name="parent_id" id="create_parent_id" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200">
                            <option value="">-- No Parent --</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @foreach($cat->children as $child)
                                    <option value="{{ $child->id }}">&nbsp;&nbsp;-- {{ $child->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="create_image" class="block text-sm font-semibold text-gray-700">Category Image</label>
                        <div class="mt-1 flex items-center gap-4">
                            <label for="create_image" class="cursor-pointer">
                                <div class="h-20 w-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center hover:border-indigo-500 transition">
                                    <i class="fas fa-upload text-gray-400"></i>
                                </div>
                            </label>
                            <input type="file" name="image" id="create_image" class="hidden" accept="image/*">
                            <span class="text-sm text-gray-500">Upload image (optional)</span>
                        </div>
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
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="closeModals()" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
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
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
        <div x-show="showEditModal" @click.outside="closeModals()" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Category</h3>
                <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="" id="editForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="slug" id="edit_slug" value="">
                <div class="space-y-4">
                    <div>
                        <label for="edit_name" class="block text-sm font-semibold text-gray-700">Category Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200"
                            placeholder="e.g., Electronics" required>
                    </div>
                    <div>
                        <label for="edit_parent_id" class="block text-sm font-semibold text-gray-700">Parent Category</label>
                        <select name="parent_id" id="edit_parent_id" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 text-sm
                                   focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none
                                   hover:border-indigo-400 transition duration-200">
                            <option value="">-- No Parent --</option>
                            @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @foreach($cat->children as $child)
                                    <option value="{{ $child->id }}">&nbsp;&nbsp;-- {{ $child->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Category Image</label>
                        <div class="mt-1 flex items-center gap-4">
                            <div class="h-20 w-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center overflow-hidden" id="editImagePreview">
                                <img x-show="editImage" :src="editImage" class="h-full w-full object-cover">
                                <i x-show="!editImage" class="fas fa-image text-gray-400"></i>
                            </div>
                            <label for="edit_image" class="cursor-pointer">
                                <div class="h-10 px-4 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg flex items-center justify-center transition">
                                    <span class="text-sm text-gray-600">Change Image</span>
                                </div>
                            </label>
                            <input type="file" name="image" id="edit_image" class="hidden" accept="image/*">
                        </div>
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
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="closeModals()" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200">
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
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals()"></div>
        <div x-show="showDeleteModal" @click.outside="closeModals()" class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all sm:align-middle">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Confirm Delete</h3>
                <button @click="closeModals()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="py-4">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <template x-if="deleteHasChildren">
                    <div class="text-center mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-yellow-700 text-sm">This category has subcategories. Please delete or reassign them first.</p>
                    </div>
                </template>
                <p class="text-center text-gray-600">Are you sure you want to delete this category?</p>
                <p class="text-center text-gray-500 text-sm mt-2"><span x-text="deleteCategoryName" class="font-semibold"></span></p>
            </div>
            <form method="POST" action="" id="deleteForm">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeModals()" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 font-medium rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <template x-if="!deleteHasChildren">
                        <button type="submit" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200">
                            Delete
                        </button>
                    </template>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script>
function categoryModalManager() {
    return {
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false,
        deleteCategoryName: '',
        deleteHasChildren: false,
        editImage: '',
        editParentId: '',

        editModal(category) {
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_slug').value = category.slug || '';
            document.getElementById('edit_parent_id').value = category.parent_id || '';
            document.getElementById('edit_status').value = category.status;
            document.getElementById('editForm').action = '/categories/' + category.id;
            this.editImage = category.image ? '/categories/' + category.image : '';
            this.editParentId = category.parent_id || '';
            this.showEditModal = true;
        },

        deleteModal(category) {
            this.deleteCategoryName = category.name;
            this.deleteHasChildren = category.children_count > 0;
            document.getElementById('deleteForm').action = '/categories/' + category.id;
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
