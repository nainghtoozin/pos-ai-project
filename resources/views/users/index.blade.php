@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Users</h3>
                    <p class="text-sm text-gray-500">Manage system users</p>
                </div>
            </div>
            @can('user.create')
                <a href="{{ route('users.create') }}"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition duration-200 transform hover:scale-[1.02] shadow-sm">
                    <i class="fas fa-plus"></i>Create User
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
            <form method="GET" action="{{ route('users.index') }}" class="flex gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" 
                        placeholder="Search users..."
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
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="even:bg-gray-50 hover:bg-indigo-50 transition duration-150">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-600 text-sm font-semibold">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @if($user->roles->first())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-full">
                                <i class="fas fa-user-shield"></i>
                                {{ $user->roles->first()->name }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                                No Role
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            @can('user.view')
                            <a href="{{ route('users.show', $user) }}" 
                                class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition duration-200"
                                title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                            @can('user.edit')
                            <a href="{{ route('users.edit', $user) }}" 
                                class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition duration-200"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('user.delete')
                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
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
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-users text-gray-300 text-4xl"></i>
                            <p class="text-gray-500 font-medium">No users found</p>
                            <p class="text-gray-400 text-sm">Try adjusting your search criteria</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="p-6 border-t border-gray-200 bg-gray-50">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
