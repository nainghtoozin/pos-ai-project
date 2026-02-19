<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'MyPOS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .animate-fade-in-left {
            animation: fadeInLeft 0.8s ease-out forwards;
        }
        .animate-fade-in-right {
            animation: fadeInRight 0.8s ease-out forwards;
        }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
    </style>
</head>
<body class="min-h-screen">
    <div class="min-h-screen flex">
        <!-- Left Side - Gradient Background -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-600 relative overflow-hidden">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0">
                <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-20 right-10 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse delay-200"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-12 lg:px-16 w-full text-white opacity-0 animate-fade-in-left">
                <div class="mb-8">
                    <h1 class="text-5xl lg:text-6xl font-bold tracking-tight mb-4">MyPOS</h1>
                    <p class="text-xl text-indigo-100 font-medium">Modern Point of Sale System</p>
                </div>
                
                <p class="text-lg text-indigo-100/80 mb-8 max-w-md">
                    Streamline your retail operations with our powerful POS solution. 
                    Manage inventory, track sales, and grow your business with real-time insights.
                </p>
                
                <div class="flex items-center gap-4">
                    <a href="#" class="px-8 py-3 bg-white/10 hover:bg-white/20 border border-white/30 rounded-full font-medium transition-all duration-300 hover:scale-105 backdrop-blur-sm">
                        Learn More
                    </a>
                </div>
                
                <!-- Features List -->
                <div class="mt-12 grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <span class="text-sm text-indigo-100">Inventory Management</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="text-sm text-indigo-100">Sales Analytics</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="text-sm text-indigo-100">Customer Database</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <span class="text-sm text-indigo-100">Invoice Printing</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 bg-white flex items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-md opacity-0 animate-fade-in-right delay-200">
                <!-- Mobile Header -->
                <div class="lg:hidden text-center mb-8">
                    <h1 class="text-3xl font-bold text-indigo-600 mb-2">MyPOS</h1>
                    <p class="text-gray-500">Point of Sale System</p>
                </div>

                <!-- Login Card -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 lg:p-10">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Hello Again!</h2>
                        <p class="text-gray-500">Welcome back to MyPOS</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="space-y-5">
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input 
                                    id="email" 
                                    name="email" 
                                    type="email" 
                                    value="{{ old('email') }}" 
                                    required 
                                    autofocus 
                                    autocomplete="username"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                                    placeholder="Enter your email"
                                >
                                @error('email')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                <input 
                                    id="password" 
                                    name="password" 
                                    type="password" 
                                    required 
                                    autocomplete="current-password"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 outline-none"
                                    placeholder="Enter your password"
                                >
                                @error('password')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center justify-between">
                                <label class="flex items-center">
                                    <input type="checkbox" name="remember" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            <!-- Login Button -->
                            <button 
                                type="submit" 
                                class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold rounded-xl transition-all duration-300 hover:scale-[1.02] hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Sign In
                            </button>
                        </div>
                    </form>

                    <!-- Register Link -->
                    @if (Route::has('register'))
                        <div class="mt-6 text-center">
                            <p class="text-sm text-gray-600">
                                Don't have an account? 
                                <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                                    Create Account
                                </a>
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <p class="text-center text-sm text-gray-400 mt-8">
                    &copy; {{ date('Y') }} MyPOS. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
