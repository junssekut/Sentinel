<nav x-data="{ open: false }" class="bg-navy border-b border-navy-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <svg class="w-8 h-8 text-sentinel-blue" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 4.84 9.74 10 11 5.16-1.26 10-5.45 10-11V7L12 2zm0 2.18l8 4v9.73c-.01 4.3-3.78 7.54-8 8.67-4.22-1.13-7.99-4.37-8-8.67v-9.73l8-4z"/>
                            <path d="M12 11a2 2 0 100-4 2 2 0 000 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <span class="text-white font-bold text-lg">Sentinel</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-8 sm:flex">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-white bg-white/10 rounded-lg' : 'text-white/70 hover:text-white' }} transition-colors">
                        Dashboard
                    </a>
                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('tasks.*') ? 'text-white bg-white/10 rounded-lg' : 'text-white/70 hover:text-white' }} transition-colors">
                        Tasks
                    </a>
                    @if(auth()->user()->isDcfm() || auth()->user()->isSoc())
                    <a href="{{ route('users.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('users.*') ? 'text-white bg-white/10 rounded-lg' : 'text-white/70 hover:text-white' }} transition-colors">
                        Users
                    </a>
                    <a href="{{ route('gates.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('gates.*') ? 'text-white bg-white/10 rounded-lg' : 'text-white/70 hover:text-white' }} transition-colors">
                        Gates
                    </a>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-white/80 hover:text-white focus:outline-none transition-colors">
                            <span class="mr-2 px-2 py-0.5 rounded text-xs font-medium bg-sentinel-blue text-white">{{ strtoupper(Auth::user()->role) }}</span>
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
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
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white/70 hover:text-white hover:bg-white/10 focus:outline-none transition-colors">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-navy-100">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('dashboard') ? 'text-white bg-white/10 rounded-lg' : 'text-white/70' }}">Dashboard</a>
            <a href="{{ route('tasks.index') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('tasks.*') ? 'text-white bg-white/10 rounded-lg' : 'text-white/70' }}">Tasks</a>
            @if(auth()->user()->isDcfm() || auth()->user()->isSoc())
            <a href="{{ route('users.index') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('users.*') ? 'text-white bg-white/10 rounded-lg' : 'text-white/70' }}">Users</a>
            <a href="{{ route('gates.index') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('gates.*') ? 'text-white bg-white/10 rounded-lg' : 'text-white/70' }}">Gates</a>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-white/10">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-white/60">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1 px-4">
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-base font-medium text-white/70">Profile</a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2 text-base font-medium text-white/70">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</nav>

