<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MyPOS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-fade-in-left { animation: fadeInLeft 0.8s ease-out forwards; }
        .animate-fade-in-right { animation: fadeInRight 0.8s ease-out forwards; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .animate-float { animation: float 6s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen">
    <div class="min-h-screen flex">
        <!-- Left Side - Gradient Background -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-600 relative overflow-hidden">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0">
                <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-float"></div>
                <div class="absolute bottom-20 right-10 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-float delay-200"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-12 lg:px-16 w-full text-white opacity-0 animate-fade-in-left">
                <div class="mb-8">
                    <h1 class="text-5xl lg:text-6xl font-bold tracking-tight mb-4">MyPOS</h1>
                    <p class="text-xl text-indigo-100 font-medium">Modern Point of Sale System</p>
                </div>
                
                <p class="text-lg text-indigo-100/80 mb-8 max-w-md">
                    Streamline your retail operations with powerful inventory management, 
                    real-time sales tracking, and comprehensive reporting.
                </p>
                
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="px-8 py-3 bg-white/10 hover:bg-white/20 border border-white/30 rounded-full font-medium transition-all duration-300 hover:scale-105 backdrop-blur-sm">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Side - Auth Form -->
        <div class="w-full lg:w-1/2 bg-white flex items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-md opacity-0 animate-fade-in-right delay-200">
                <!-- Mobile Header -->
                <div class="lg:hidden text-center mb-8">
                    <h1 class="text-3xl font-bold text-indigo-600 mb-2">MyPOS</h1>
                    <p class="text-gray-500">Point of Sale System</p>
                </div>

                @yield('content')

                <!-- Footer -->
                <p class="text-center text-sm text-gray-400 mt-8">
                    &copy; {{ date('Y') }} MyPOS. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
