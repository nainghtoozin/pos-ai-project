@extends('layouts.app')

@section('title', 'Role Details')
@section('page-title', 'Role Details')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-900">{{ $role->name }}</h3>
            <p class="text-gray-600">Guard: {{ $role->guard_name }}</p>
        </div>

        <div class="mb-6">
            <h4 class="text-lg font-semibold mb-4">Permissions</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($role->permissions as $permission)
                    <div class="bg-gray-100 p-3 rounded-lg text-sm">
                        {{ $permission->name }}
                    </div>
                @empty
                    <p class="text-gray-500">No permissions assigned</p>
                @endforelse
            </div>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg mr-2 transition duration-300">Back</a>
            @can('role.edit')
            <a href="{{ route('roles.edit', $role) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 transform hover:scale-105">Edit</a>
            @endcan
        </div>
    </div>
</div>
@endsection
