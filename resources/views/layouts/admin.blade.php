<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - WeFashion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-inter antialiased bg-gray-50 text-gray-800" x-data="{ sidebarOpen: false, showLogoutModal: false }">
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden"
         @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 w-64 transform bg-gradient-to-b from-indigo-700 to-indigo-800 text-white transition-all duration-300 ease-in-out lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="flex flex-col h-full">
            <!-- Brand Logo -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-indigo-600">
                <div class="flex items-center space-x-3">
                    <svg class="w-8 h-8 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span class="text-xl font-bold">WeFashion</span>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-indigo-200 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 overflow-y-auto">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 
                                  {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-indigo-600 hover:bg-opacity-50' }}">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.produk') }}" 
                           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 
                                  {{ request()->routeIs('admin.produk*') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-indigo-600 hover:bg-opacity-50' }}">
                            <i class="fas fa-tshirt mr-3"></i>
                            <span>Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.pengguna') }}" 
                           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 
                                  {{ request()->routeIs('admin.pengguna*') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-indigo-600 hover:bg-opacity-50' }}">
                            <i class="fas fa-users mr-3"></i>
                            <span>Pengguna</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.kategori') }}" 
                           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 
                                  {{ request()->routeIs('admin.kategori*') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-indigo-600 hover:bg-opacity-50' }}">
                            <i class="fas fa-tags mr-3"></i>
                            <span>Kategori</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" 
                           class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 text-indigo-100 hover:bg-indigo-600 hover:bg-opacity-50">
                            <i class="fas fa-shopping-cart mr-3"></i>
                            <span>Pesanan</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- User Profile & Logout -->
            <div class="px-4 py-6 border-t border-indigo-600">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-indigo-200">Admin</p>
                    </div>
                </div>
                <button @click="showLogoutModal = true" class="w-full flex items-center px-4 py-2 text-indigo-100 hover:bg-indigo-600 rounded-lg transition-all duration-200">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Logout Confirmation Modal -->
    <div x-show="showLogoutModal" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden w-full max-w-md mx-4"
             @click.away="showLogoutModal = false">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <i class="fas fa-exclamation text-red-600 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Konfirmasi Logout</h3>
                <p class="text-sm text-gray-500 text-center mb-6">Apakah Anda yakin ingin keluar dari admin panel?</p>
                
                <div class="flex justify-center space-x-4">
                    <button @click="showLogoutModal = false" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Ya, Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm">
            <div class="flex items-center justify-between px-6 py-4">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Search & Notifications -->
                <div class="flex-1 flex justify-end items-center space-x-4">
                    <div class="relative max-w-md w-full lg:block hidden">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search...">
                    </div>
                    
                    <button class="p-2 text-gray-500 hover:text-gray-700 relative">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>

    <!-- Alpine JS -->
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
</body>
</html>