@extends('layouts.guest')

@section('content')
<!-- Session Status -->
@if (session('status'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-700">{{ session('status') }}</p>
    </div>
@endif

<!-- Forgot Password Card -->
<div class="bg-white shadow-lg rounded-xl p-8">
    <h2 class="text-2xl font-bold text-center text-gray-900 mb-2">Forgot Password?</h2>
    <p class="text-sm text-gray-600 text-center mb-6">Enter your email and we'll send you a reset link.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="space-y-5">
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    autocomplete="username"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                    placeholder="Enter your email"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <button 
                type="submit" 
                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition"
            >
                Send Reset Link
            </button>
        </div>
    </form>

    <!-- Back to Login -->
    <p class="mt-6 text-center text-sm text-gray-600">
        Remember your password? 
        <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
            Login
        </a>
    </p>
</div>
@endsection
