@extends('layouts.app')

@section('title', 'Edit Role')
@section('page-title', 'Edit Role')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
        <form method="POST" action="{{ route('roles.update', $role) }}" x-data="roleManager()">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Role Information</h3>

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="guard_name" class="block text-sm font-medium text-gray-700">Guard Name</label>
                        <input type="text" name="guard_name" id="guard_name" value="{{ old('guard_name', $role->guard_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('guard_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Permissions</h3>
                    <p class="text-sm text-gray-600 mb-4">Selected: <span x-text="selectedPermissions.length" class="font-semibold"></span></p>

                    @forelse($groupedPermissions as $group => $permissions)
                    @php 
                        $moduleName = explode(' ', $group)[0];
                        $modulePrefix = strtolower($moduleName);
                    @endphp
                    <div class="mb-4">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800">{{ $group }}</h4>
                                <button type="button" @click="toggleGroup('{{ $group }}')" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                    <span x-show="!openGroups['{{ $group }}']">Show</span>
                                    <span x-show="openGroups['{{ $group }}']">Hide</span>
                                </button>
                            </div>
                            <div x-show="openGroups['{{ $group }}']" x-transition class="grid grid-cols-2 gap-2">
                                @foreach($permissions as $permission)
                                <label class="flex items-center p-2 rounded hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @change="updateSelected" 
                                        {{ $role->permissions->contains('name', $permission->name) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $permission->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500">No permissions available.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg mr-2 transition duration-300">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 transform hover:scale-105">Update Role</button>
            </div>
        </form>
    </div>
</div>

<script>
function roleManager() {
    return {
        openGroups: {!! json_encode($groupedPermissions->mapWithKeys(fn($v, $k) => [$k => true])->toArray()) !!},
        selectedPermissions: {!! json_encode($role->permissions->pluck('name')->toArray()) !!},
        toggleGroup(group) {
            this.openGroups[group] = !this.openGroups[group];
        },
        updateSelected() {
            const checkboxes = document.querySelectorAll('input[name="permissions[]"]:checked');
            this.selectedPermissions = Array.from(checkboxes).map(cb => cb.value);
        }
    }
}
</script>
@endsection
