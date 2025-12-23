<nav x-data="{ open: false }" class="bg-navy-900 border-b border-navy-700 sticky top-0 z-30 shadow-lg">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center group">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="relative">
                            <div class="absolute inset-0 bg-sentinel-blue/50 blur-lg rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <img src="{{ asset('sentinel-logo.png') }}" alt="Sentinel" class="w-10 h-10 relative z-10 drop-shadow-md">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-white font-display font-bold text-xl tracking-wide leading-none group-hover:text-blue-100 transition-colors">SENTINEL</span>
                            <span class="text-blue-300 text-[0.65rem] font-sans tracking-widest uppercase leading-none mt-1">Access Control</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex h-full items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('tasks.*') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                        Tasks
                    </a>
                    
                    @if(auth()->user()->isDcfm() || auth()->user()->isSoc())
                        <div class="h-6 w-px bg-white/10 mx-2"></div>
                        <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('users.*') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            Users
                        </a>
                        <a href="{{ route('vendors.pending') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 relative {{ request()->routeIs('vendors.pending') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            Pending
                            @if(\App\Models\User::where('face_approved', false)->where('role', 'vendor')->count() > 0)
                                <span class="absolute -top-1 -right-1 w-2 h-2 bg-error rounded-full animate-pulse"></span>
                            @endif
                        </a>
                        <a href="{{ route('gates.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('gates.*') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            Gates
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-300 hover:text-white focus:outline-none transition-colors group">
                            <div class="text-right hidden md:block">
                                <div class="text-xs text-blue-300 uppercase tracking-wide">{{ Auth::user()->role }}</div>
                                <div class="font-bold text-white leading-tight">{{ Auth::user()->name }}</div>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-sentinel-gradient p-[2px] shadow-lg group-hover:shadow-glow transition-all duration-300">
                                <div class="w-full h-full rounded-full bg-navy-900 border-2 border-transparent flex items-center justify-center overflow-hidden">
                                     <span class="font-display text-white font-bold text-lg">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 focus:outline-none transition-all duration-200">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-navy-800 border-t border-white/10">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('dashboard') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                Dashboard
            </a>
            <a href="{{ route('tasks.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('tasks.*') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                Tasks
            </a>
            
            @if(auth()->user()->isDcfm() || auth()->user()->isSoc())
                <div class="border-t border-white/10 my-2"></div>
                <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('users.*') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    Users
                </a>
                <a href="{{ route('vendors.pending') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('vendors.pending') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    Pending Approvals
                </a>
                <a href="{{ route('gates.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('gates.*') ? 'text-white bg-sentinel-blue' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    Gates
                </a>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-4 border-t border-white/10 bg-black/20">
            <div class="px-6 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-sentinel-gradient flex items-center justify-center text-white font-bold shadow-md">
                     {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-400">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1 px-4">
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-300 hover:text-white hover:bg-white/10">
                    Profile
                </a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2 rounded-lg text-base font-medium text-gray-300 hover:text-white hover:bg-white/10">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

