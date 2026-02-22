@extends('layouts.app')

@section('title', 'Add Supplier')
@section('page-title', 'Add Supplier')

@section('content')
<form method="POST" action="{{ route('suppliers.store') }}">
    @csrf

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-user text-indigo-600"></i>
                Basic Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none @error('name') border-red-500 @enderror"
                        placeholder="Enter supplier name">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="mobile" class="block text-sm font-medium text-gray-700 mb-2">Mobile <span class="text-red-500">*</span></label>
                    <input type="text" name="mobile" id="mobile" value="{{ old('mobile') }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none @error('mobile') border-red-500 @enderror"
                        placeholder="Enter mobile number">
                    @error('mobile')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-indigo-600"></i>
                Address Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea name="address" id="address" rows="2"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        placeholder="Enter address">{{ old('address') }}</textarea>
                </div>
                <div>
                    <label for="township" class="block text-sm font-medium text-gray-700 mb-2">Township</label>
                    <input type="text" name="township" id="township" value="{{ old('township') }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        placeholder="Enter township">
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        placeholder="Enter city">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-link text-indigo-600"></i>
                Other Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="social_profile" class="block text-sm font-medium text-gray-700 mb-2">Social Profile Link</label>
                    <input type="text" name="social_profile" id="social_profile" value="{{ old('social_profile') }}"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        placeholder="Facebook, Viber, etc.">
                </div>
                <div>
                    <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-2">Opening Balance</label>
                    <input type="number" name="opening_balance" id="opening_balance" value="{{ old('opening_balance', 0) }}"
                        min="0" step="0.01"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        placeholder="0.00">
                </div>
                <div>
                    <label for="advance_balance" class="block text-sm font-medium text-gray-700 mb-2">Advance Balance</label>
                    <input type="number" name="advance_balance" id="advance_balance" value="{{ old('advance_balance', 0) }}"
                        min="0" step="0.01"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        placeholder="0.00" readonly>
                    <p class="mt-1 text-xs text-gray-500">Future feature - Coming soon</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('suppliers.index') }}" 
                class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition duration-200">
                Cancel
            </a>
            <button type="submit" 
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-200 shadow-sm">
                Save Supplier
            </button>
        </div>
    </div>
</form>
@endsection
