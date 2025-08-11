<nav class="flex justify-between items-center px-6 py-4 border-b bg-white">
    <!-- Logo -->
    <div class="text-lg font-semibold">
        <a href="{{ url('/') }}" class="hover:text-pink-500 font-playfair">WEFASHION</a>
    </div>
    
    <!-- Menu Items -->
    <ul class="flex space-x-6 items-center text-sm">
        <li>
            <a href="{{ url('/') }}" class="hover:text-pink-500 {{ request()->is('/') ? 'text-pink-500' : '' }}">
                Home
            </a>
        </li>
        <li>
            <a href="{{ route('katalog') }}" class="hover:text-pink-500 {{ request()->is('katalog*') ? 'text-pink-500' : '' }}">
                Katalog
            </a>
        </li>
        <li>
            <a href="#" class="hover:text-pink-500">
                <i class="fas fa-search"></i>
            </a>
        </li>
        
        <!-- Cart Icon (Visible when logged in) -->
        @auth
            <li>
                <a href="{{ route('cart.index') }}" class="hover:text-pink-500 relative flex items-center">
                    <!-- Shopping bag icon with handle -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    @if(auth()->user()->cartItems->count() > 0)
                        <span class="absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ auth()->user()->cartItems->count() }}
                        </span>
                    @endif
                </a>
            </li>
        @endauth
        
        <!-- Auth Links -->
        @auth
            <li class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center hover:text-pink-500 focus:outline-none">
                    <!-- Profile Picture or Initials -->
                    @if(Auth::user()->profile_photo_path)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" 
                             alt="{{ Auth::user()->name }}"
                             class="h-8 w-8 rounded-full object-cover border border-gray-200">
                    @else
                        <div class="h-8 w-8 rounded-full bg-pink-500 flex items-center justify-center text-white font-medium">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div x-show="open" @click.away="open = false" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-100">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-pink-50 hover:text-pink-600">
                        <i class="fas fa-user mr-2"></i> Profil Saya
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
            
            <!-- Register Link -->
            <li>
                <a href="{{ route('register') }}" class="hover:text-pink-500" title="Register">
                    <i class="fas fa-user-plus"></i>
                    <span class="hidden md:inline ml-1">Register</span>
                </a>
            </li>
        @endauth
    </ul>
</nav>