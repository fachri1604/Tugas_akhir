<nav class="flex justify-between items-center px-6 py-4 border-b bg-white">
    <!-- Logo -->
    <div class="text-lg font-semibold">
        <a href="{{ url('/') }}" class="hover:text-pink-500 font-playfair">WEFASHION</a>
    </div>
    
    <!-- Menu Items -->
    <ul class="flex space-x-6 items-center text-sm">
        <li>
            <a href="{{ url('/') }}" class="hover:text-pink-500 {{ request()->is('/') }}">
                Home
            </a>
        </li>
        <li>
            {{-- <a href="{{ route('produk.index') }}" class="hover:text-pink-500 {{ request()->is('products*') ? 'text-pink-500' : '' }}">
                Produk
            </a> --}}
        </li>
        <li>
            <a href="#" class="hover:text-pink-500">
                <i class="fas fa-search"></i>
            </a>
        </li>
        
        <!-- Auth Links -->
        @auth
            <li class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center hover:text-pink-500">
                    <i class="fas fa-user-circle mr-1"></i>
                    <span class="hidden md:inline">{{ Str::limit(Auth::user()->name, 10) }}</span>
                    <i class="fas fa-chevron-down ml-1 text-xs"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div x-show="open" @click.away="open = false" 
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-pink-50 hover:text-pink-600">
                        <i class="fas fa-user-edit mr-2"></i> Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm hover:bg-pink-50 hover:text-pink-600">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        @else
            <!-- Login Link -->
            <li>
                <a href="{{ route('login') }}" class="hover:text-pink-500" title="Login">
                    <i class="fas fa-sign-in-alt"></i>
                    <span class="hidden md:inline ml-1">Login</span>
                </a>
            </li>
            
            <!-- Register Link (New) -->
            <li>
                <a href="{{ route('register') }}" class="hover:text-pink-500" title="Register">
                    <i class="fas fa-user-plus"></i>
                    <span class="hidden md:inline ml-1">Register</span>
                </a>
            </li>
        @endauth
    </ul>
</nav>