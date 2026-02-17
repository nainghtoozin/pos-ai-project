@extends('layouts.app')

@section('title', 'Roles Management')
@section('page-title', 'Roles Management')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-shield text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Roles</h3>
                    <p class="text-sm text-gray-500">Manage user roles and permissions</p>
                </div>
            </div>
            @can('role.create')
                <a href="{{ route('roles.create') }}"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition duration-200 transform hover:scale-[1.02] shadow-sm">
                    <i class="fas fa-plus"></i>Create Role
                </a>
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
            <form method="GET" action="{{ route('roles.index') }}" class="flex gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" 
                        placeholder="Search roles..."
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
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role Name</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Permissions</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($roles as $role)
                <tr class="even:bg-gray-50 hover:bg-indigo-50 transition duration-150">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-indigo-600 text-sm"></i>
                            </div>
                            <span class="text-sm font-semibold text-gray-800">{{ $role->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">
                            <i class="fas fa-key"></i>
                            {{ $role->permissions->count() }} permissions
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            @can('role.view')
                            <a href="{{ route('roles.show', $role) }}" 
                                class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition duration-200"
                                title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                            @can('role.edit')
                            <a href="{{ route('roles.edit', $role) }}" 
                                class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition duration-200"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('role.delete')
                            <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                    class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition duration-200"
                                    title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-user-shield text-gray-300 text-4xl"></i>
                            <p class="text-gray-500 font-medium">No roles found</p>
                            <p class="text-gray-400 text-sm">Try adjusting your search criteria</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($roles->hasPages())
    <div class="p-6 border-t border-gray-200 bg-gray-50">
        {{ $roles->links() }}
    </div>
    @endif
</div>
@endsection
