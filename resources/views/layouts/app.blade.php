<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POS System') }} - @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Figtree', sans-serif; }
        .sidebar { transition: transform 0.3s ease; }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: #1e293b; }
        .sidebar::-webkit-scrollbar-thumb { background: #475569; border-radius: 3px; }
        .submenu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .submenu.active { max-height: 500px; }
    </style>
    @yield('styles')
</head>
<body class="bg-gray-100 antialiased">
    <div x-data="layoutState()">
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeSidebar()"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden"
             x-cloak>
        </div>

        <!-- Desktop Sidebar (always visible on lg+) -->
        <aside class="hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 lg:left-0 lg:w-72 bg-slate-800 text-white overflow-y-auto">
            <div class="p-4 border-b border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cash-register text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">POS System</h1>
                        <p class="text-xs text-slate-400">Management</p>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-1 flex-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-500 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>

                <div class="menu-item" x-data="{ open: {{ in_array('users-menu', old('open_menus', [])) || request()->is('users*') || request()->is('roles*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-users w-5"></i>
                            <span>User Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('users.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-user w-4"></i>
                            <span>Users</span>
                        </a>
                        <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('roles.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-user-shield w-4"></i>
                            <span>Roles</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: {{ request()->is('products*') || request()->is('categories*') || request()->is('units*') || request()->is('brands*') || request()->is('stocks*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-box w-5"></i>
                            <span>Product Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('products.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-box-open w-4"></i>
                            <span>Products</span>
                        </a>
                        <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('categories.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-tags w-4"></i>
                            <span>Categories</span>
                        </a>
                        <a href="{{ route('brands.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('brands.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-copyright w-4"></i>
                            <span>Brands</span>
                        </a>
                        <a href="{{ route('units.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('units.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-balance-scale w-4"></i>
                            <span>Units</span>
                        </a>
                        <a href="{{ route('stocks.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('stocks.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-warehouse w-4"></i>
                            <span>Stock Management</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shopping-cart w-5"></i>
                            <span>Sales Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-calculator w-4"></i>
                            <span>POS / New Sale</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-list w-4"></i>
                            <span>Sales List</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-undo w-4"></i>
                            <span>Sales Returns</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-file-alt w-4"></i>
                            <span>Draft Sales</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shopping-bag w-5"></i>
                            <span>Purchase Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-cart-plus w-4"></i>
                            <span>Purchases</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-truck w-4"></i>
                            <span>Suppliers</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-undo-alt w-4"></i>
                            <span>Purchase Returns</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user-friends w-5"></i>
                            <span>Customer Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-users w-4"></i>
                            <span>Customers</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-users-cog w-4"></i>
                            <span>Customer Groups</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-wallet w-5"></i>
                            <span>Accounts & Finance</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-receipt w-4"></i>
                            <span>Expenses</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-hand-holding-usd w-4"></i>
                            <span>Income</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-chart-pie w-4"></i>
                            <span>Reports</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-money-bill-wave w-4"></i>
                            <span>Profit/Loss Report</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-bar w-5"></i>
                            <span>Reports</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-calendar-day w-4"></i>
                            <span>Daily Report</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-calendar-alt w-4"></i>
                            <span>Monthly Report</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-boxes w-4"></i>
                            <span>Stock Report</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-shopping-bag w-4"></i>
                            <span>Sales Report</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-cogs w-5"></i>
                            <span>Settings</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="{{ route('taxes.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('taxes.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-percent w-4"></i>
                            <span>Tax</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-building w-4"></i>
                            <span>Business Settings</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-sliders-h w-4"></i>
                            <span>System Settings</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Mobile Sidebar (slides in from left) -->
        <aside x-show="sidebarOpen"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-800 text-white overflow-y-auto lg:hidden"
               x-cloak>
            <div class="p-4 border-b border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cash-register text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">POS System</h1>
                        <p class="text-xs text-slate-400">Management</p>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-1">
                <a href="{{ route('dashboard') }}" @click="closeSidebar()" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-500 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-users w-5"></i>
                            <span>User Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="{{ route('users.index') }}" @click="closeSidebar()" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-user w-4"></i>
                            <span>Users</span>
                        </a>
                        <a href="{{ route('roles.index') }}" @click="closeSidebar()" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-user-shield w-4"></i>
                            <span>Roles</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-box w-5"></i>
                            <span>Product Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="{{ route('products.index') }}" @click="closeSidebar()" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('products.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-box-open w-4"></i>
                            <span>Products</span>
                        </a>
                        <a href="{{ route('categories.index') }}" @click="closeSidebar()" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-tags w-4"></i>
                            <span>Categories</span>
                        </a>
                        <a href="{{ route('brands.index') }}" @click="closeSidebar()" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-copyright w-4"></i>
                            <span>Brands</span>
                        </a>
                        <a href="{{ route('units.index') }}" @click="closeSidebar()" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-balance-scale w-4"></i>
                            <span>Units</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shopping-cart w-5"></i>
                            <span>Sales Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-calculator w-4"></i>
                            <span>POS / New Sale</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-list w-4"></i>
                            <span>Sales List</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shopping-bag w-5"></i>
                            <span>Purchase Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-cart-plus w-4"></i>
                            <span>Purchases</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user-friends w-5"></i>
                            <span>Customer Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-users w-4"></i>
                            <span>Customers</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-bar w-5"></i>
                            <span>Reports</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-calendar-day w-4"></i>
                            <span>Daily Report</span>
                        </a>
                    </div>
                </div>

                <div class="menu-item" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-cogs w-5"></i>
                            <span>Settings</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 mt-1">
                        <a href="{{ route('taxes.index') }}" @click="closeSidebar()" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition {{ request()->routeIs('taxes.*') ? 'text-white bg-slate-700' : '' }}">
                            <i class="fas fa-percent w-4"></i>
                            <span>Tax</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition">
                            <i class="fas fa-building w-4"></i>
                            <span>Business Settings</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-72 min-h-screen">
            <!-- Navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 lg:px-8 py-4">
                    <div class="flex items-center gap-4">
                        <button @click="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-bars text-gray-600 text-lg"></i>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                    </div>

                    <div class="flex items-center gap-4">
                        <button class="relative p-2 rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-bell text-gray-600"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <div class="relative" x-data="{ dropdownOpen: false }">
                            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition">
                                <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <span class="hidden sm:block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                            </button>
                            <div x-show="dropdownOpen" @click="dropdownOpen = false" class="fixed inset-0"></div>
                            <div x-show="dropdownOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50" x-cloak>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50 transition">
                                    <i class="fas fa-user w-4"></i>
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt w-4"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function layoutState() {
            return {
                sidebarOpen: false,
                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                },
                closeSidebar() {
                    this.sidebarOpen = false;
                }
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
