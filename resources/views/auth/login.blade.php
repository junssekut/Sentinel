<x-guest-layout>
    <div class="mb-6 text-center">
        <h3 class="font-display font-bold text-2xl text-navy-900">Welcome Back</h3>
        <p class="text-slate-500 font-sans text-sm">Sign in to access your dashboard</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div class="group">
            <x-input-label for="email" :value="__('Email')" class="text-navy-700 font-medium ml-1 mb-1" />
            <x-text-input id="email" class="block mt-1 w-full bg-slate-50 border-gray-200 focus:border-sentinel-blue focus:ring-sentinel-blue/20 rounded-xl py-2.5 px-4 transition-all duration-200 group-hover:bg-white" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="group">
            <x-input-label for="password" :value="__('Password')" class="text-navy-700 font-medium ml-1 mb-1" />

            <x-text-input id="password" class="block mt-1 w-full bg-slate-50 border-gray-200 focus:border-sentinel-blue focus:ring-sentinel-blue/20 rounded-xl py-2.5 px-4 transition-all duration-200 group-hover:bg-white"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-sentinel-blue shadow-sm focus:ring-sentinel-blue/20" name="remember">
                <span class="ms-2 text-sm text-slate-600 hover:text-navy-700 transition-colors">{{ __('Remember me') }}</span>
            </label>
            
            @if (Route::has('password.request'))
                <a class="underline text-sm text-sentinel-blue hover:text-sentinel-blue-dark transition-colors rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sentinel-blue" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3 bg-sentinel-gradient hover:shadow-glow hover:scale-[1.01] transition-all duration-200 rounded-xl font-bold font-bricolage text-base">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <!-- Register Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-slate-600">
                Don't have an account? 
                <a href="{{ route('register') }}" class="font-bold text-sentinel-blue hover:text-sentinel-blue-dark underline decoration-2 underline-offset-2 transition-colors">
                    Register as Vendor
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
