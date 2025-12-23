<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('gates.index') }}" class="text-slate hover:text-navy">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-navy leading-tight">{{ $gate->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                <div class="w-16 h-16 {{ $gate->is_active ? 'bg-success/10' : 'bg-slate/10' }} rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 {{ $gate->is_active ? 'text-success' : 'text-slate' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-navy text-lg text-center">{{ $gate->name }}</h3>
                <p class="text-slate text-sm text-center">{{ $gate->location ?? 'No location' }}</p>
                <div class="text-center mt-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $gate->is_active ? 'bg-success/10 text-success' : 'bg-slate/10 text-slate' }}">
                        {{ $gate->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                @can('update', $gate)
                <div class="mt-6 pt-6 border-t border-light-200">
                    <a href="{{ route('gates.edit', $gate) }}" class="block text-center px-4 py-2 border border-light-200 text-navy text-sm font-medium rounded-lg hover:bg-light-100 transition-colors">Edit Gate</a>
                </div>
                @endcan
            </div>
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                    <h3 class="font-semibold text-navy mb-4">Details</h3>
                    <dl class="space-y-3">
                        <div><dt class="text-sm text-slate">Gate ID</dt><dd class="font-mono text-navy">{{ $gate->gate_id }}</dd></div>
                        <div><dt class="text-sm text-slate">Description</dt><dd class="text-navy">{{ $gate->description ?? 'No description' }}</dd></div>
                        <div><dt class="text-sm text-slate">Created</dt><dd class="text-navy">{{ $gate->created_at->format('M d, Y H:i') }}</dd></div>
                    </dl>
                </div>
                @if($gate->tasks->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-light-200">
                    <div class="px-6 py-4 border-b border-light-200"><h3 class="font-semibold text-navy">Recent Tasks</h3></div>
                    <div class="divide-y divide-light-200">
                        @foreach($gate->tasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-4 hover:bg-light-100/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-navy">{{ $task->vendor->name }}</p>
                                    <p class="text-sm text-slate">PIC: {{ $task->pic->name }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium @if($task->status === 'active') bg-success/10 text-success @elseif($task->status === 'completed') bg-slate/10 text-slate @else bg-error/10 text-error @endif">{{ ucfirst($task->status) }}</span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
