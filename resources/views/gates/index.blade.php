<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-navy leading-tight">{{ __('Gates') }}</h2>
            @can('create', App\Models\Gate::class)
            <a href="{{ route('gates.create') }}" class="inline-flex items-center px-4 py-2 bg-sentinel-blue text-white text-sm font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Gate
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($gates as $gate)
            <div class="bg-white rounded-xl shadow-sm border border-light-200 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 {{ $gate->is_active ? 'bg-success/10' : 'bg-slate/10' }} rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $gate->is_active ? 'text-success' : 'text-slate' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gate->is_active ? 'bg-success/10 text-success' : 'bg-slate/10 text-slate' }}">
                            {{ $gate->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <h3 class="font-semibold text-navy">{{ $gate->name }}</h3>
                    <p class="text-sm text-slate mt-1">{{ $gate->location ?? 'No location' }}</p>
                    <p class="text-xs font-mono text-slate mt-2">{{ $gate->gate_id }}</p>
                </div>
                <div class="px-5 py-3 bg-light-100 border-t border-light-200 flex items-center justify-between">
                    <span class="text-xs text-slate">{{ $gate->tasks_count ?? 0 }} task(s)</span>
                    @can('update', $gate)
                    <a href="{{ route('gates.edit', $gate) }}" class="text-sm text-sentinel-blue hover:text-sentinel-blue-dark font-medium">Edit</a>
                    @endcan
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-xl shadow-sm border border-light-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-light-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-navy">No gates found</h3>
                <p class="mt-1 text-slate">Add gates to enable access control.</p>
                @can('create', App\Models\Gate::class)
                <a href="{{ route('gates.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-sentinel-blue text-white text-sm font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">Add Gate</a>
                @endcan
            </div>
            @endforelse
        </div>
        @if($gates->hasPages())
        <div class="mt-6">{{ $gates->links() }}</div>
        @endif
    </div>
</x-app-layout>
