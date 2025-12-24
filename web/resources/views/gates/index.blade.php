<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-2xl text-navy-900 leading-tight">{{ __('Gates') }}</h2>
                <p class="text-slate-500 text-sm mt-1">Manage access points and entry gates.</p>
            </div>
            @can('create', App\Models\Gate::class)
            <a href="{{ route('gates.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sentinel-gradient text-white text-sm font-bold rounded-xl hover:shadow-glow hover:scale-[1.02] transition-all duration-200 shadow-lg shadow-sentinel-blue/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Gate
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($gates as $gate)
        <div class="group bg-white rounded-2xl shadow-bento hover:shadow-bento-hover transition-all duration-300 overflow-hidden border border-gray-100 relative">
            <!-- Active Status Indicator -->
            <div class="absolute top-0 right-0 m-4 z-10">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide shadow-md
                    {{ $gate->is_active ? 'bg-success text-white border-2 border-white' : 'bg-slate-400 text-white border-2 border-white' }}">
                    {{ $gate->is_active ? '● Online' : '● Offline' }}
                </span>
            </div>

            <!-- Icon Header -->
            <div class="relative bg-gradient-to-br from-navy-900 to-navy-800 h-32 overflow-hidden">
                <div class="absolute inset-0 bg-mesh opacity-30"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-16 h-16 rounded-2xl {{ $gate->is_active ? 'bg-success/20' : 'bg-slate-400/20' }} backdrop-blur-sm flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 {{ $gate->is_active ? 'text-success' : 'text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <h3 class="font-display font-bold text-navy-900 text-lg mb-1">{{ $gate->name }}</h3>
                <p class="text-sm text-slate-500 mb-3 font-medium">{{ $gate->location ?? 'No location specified' }}</p>
                <p class="text-xs font-mono text-slate-400 bg-slate-100 px-2 py-1 rounded inline-block">{{ $gate->gate_id }}</p>
            </div>

            <div class="px-6 py-4 bg-slate-50/50 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span class="text-xs text-slate-600 font-bold">{{ $gate->tasks_count ?? 0 }} task(s)</span>
                </div>
                @can('update', $gate)
                <a href="{{ route('gates.edit', $gate) }}" class="text-sm text-sentinel-blue hover:text-sentinel-blue-dark font-bold hover:underline decoration-2 underline-offset-2">
                    Edit
                </a>
                @endcan
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white/50 backdrop-blur-sm rounded-2xl shadow-bento p-16 text-center border border-white/60">
            <div class="w-20 h-20 bg-sentinel-blue/5 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-sentinel-blue/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-lg font-display font-bold text-navy-900">No gates found</h3>
            <p class="text-slate-500 mt-2">Add gates to enable access control.</p>
            @can('create', App\Models\Gate::class)
            <a href="{{ route('gates.create') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-sentinel-gradient text-white font-bold rounded-xl hover:shadow-glow transition-all duration-200 shadow-lg shadow-sentinel-blue/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add First Gate
            </a>
            @endcan
        </div>
        @endforelse
    </div>
    @if($gates->hasPages())
    <div class="mt-8">{{ $gates->links() }}</div>
    @endif
</x-app-layout>
