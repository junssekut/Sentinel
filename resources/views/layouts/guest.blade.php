<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-navy-900 antialiased bg-light-100">
        <div class="min-h-screen flex">
            <!-- Left Side: Branding -->
            <div class="hidden lg:flex lg:w-1/2 bg-sentinel-gradient relative overflow-hidden items-center justify-center">
                <!-- Abstract Shapes/Gradients -->
                <div class="absolute inset-0 opacity-20 bg-hero-pattern"></div>
                <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-900/40 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
                
                <div class="relative z-10 text-center text-white p-12">
                    <img src="{{ asset('sentinel-logo.png') }}" alt="Sentinel" class="w-72 h-72 relative z-10 drop-shadow-md mx-auto mb-8">
                </div>
            </div>

            <!-- Right Side: Content -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 bg-white/50 backdrop-blur-3xl">
                <div class="w-full max-w-md">
                    <!-- Mobile Logo (visible only on small screens) -->
                    <div class="lg:hidden flex justify-center mb-8">
                        <x-application-logo class="w-16 h-16 fill-current text-sentinel-blue" />
                    </div>

                    <div class="bg-white/80 p-8 rounded-2xl shadow-bento border border-white/50">
                        {{ $slot }}
                    </div>
                    
                    <div class="mt-8 text-center text-sm text-slate-400 font-bricolage">
                        &copy; {{ date('Y') }} Sentinel System. Secured & Monitored.
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
