<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-2xl text-navy-900 leading-tight">
                    {{ __('Tasks') }}
                </h2>
                <p class="text-slate-500 text-sm mt-1">Manage vendor access assignments and permissions.</p>
            </div>
            @can('create', App\Models\Task::class)
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sentinel-gradient text-white text-sm font-bold rounded-xl hover:shadow-glow hover:scale-[1.02] transition-all duration-200 shadow-lg shadow-sentinel-blue/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Task
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filters Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-bento border border-white/60 p-5">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-bold text-navy-900 mb-2">Filter by Status</label>
                    <select name="status" class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-colors font-medium">
                        <option value="">All Tasks</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-navy-900 text-white text-sm font-bold rounded-xl hover:bg-navy-800 transition-all duration-200 hover:shadow-lg">
                    Apply Filter
                </button>
            </form>
        </div>

        <!-- Tasks Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($tasks as $task)
            <div class="group bg-white rounded-2xl shadow-bento hover:shadow-bento-hover transition-all duration-300 overflow-hidden border border-gray-100 relative">
                <!-- Status Indicator Bar -->
                <div class="absolute top-0 left-0 right-0 h-1 
                    @if($task->status === 'active') bg-success
                    @elseif($task->status === 'completed') bg-slate-400
                    @else bg-error @endif"></div>
                
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-sentinel-blue/10 flex items-center justify-center text-sentinel-blue font-display font-bold text-lg shadow-sm">
                                {{ substr($task->title ?? 'T', 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-display font-bold text-navy-900">{{ $task->title ?? 'Untitled Task' }}</h3>
                                <p class="text-sm text-slate-500">
                                    @if($task->vendors->isNotEmpty())
                                        {{ $task->vendors->pluck('name')->join(', ') }}
                                    @else
                                        No vendors assigned
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-sm mb-4">
                        <div class="flex items-center gap-2 text-slate-600">
                            <svg class="w-4 h-4 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="font-medium">PIC: {{ $task->pic->name }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600">
                            <svg class="w-4 h-4 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-mono text-xs">{{ $task->start_time->format('M d, H:i') }} - {{ $task->end_time->format('H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600">
                            <svg class="w-4 h-4 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                            <span class="font-medium">{{ $task->gates->count() }} gate(s) allowed</span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50/50 border-t border-gray-100 flex items-center justify-between">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                        @if($task->status === 'active') bg-success/10 text-success border border-success/20
                        @elseif($task->status === 'completed') bg-slate-100 text-slate-600 border border-slate-200
                        @else bg-error/10 text-error border border-error/20 @endif">
                        {{ $task->status }}
                    </span>
                    
                    <div class="flex items-center gap-2">
                        <a href="{{ route('tasks.show', $task) }}" class="text-sm text-sentinel-blue hover:text-sentinel-blue-dark font-bold hover:underline decoration-2 underline-offset-2">
                            View
                        </a>
                        @if($task->status === 'active' && auth()->user()->isDcfm())
                        <div class="h-4 w-px bg-gray-300"></div>
                        <form action="{{ route('tasks.complete', $task) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-success hover:text-success/80 font-bold">Complete</button>
                        </form>
                        <form action="{{ route('tasks.revoke', $task) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-error hover:text-error/80 font-bold">Revoke</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white/50 backdrop-blur-sm rounded-2xl shadow-bento p-16 text-center border border-white/60">
                <div class="w-20 h-20 bg-sentinel-blue/5 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-sentinel-blue/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h3 class="text-lg font-display font-bold text-navy-900">No tasks found</h3>
                <p class="text-slate-500 mt-2">Get started by creating a new task assignment.</p>
                @can('create', App\Models\Task::class)
                <a href="{{ route('tasks.create') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-sentinel-gradient text-white font-bold rounded-xl hover:shadow-glow transition-all duration-200 shadow-lg shadow-sentinel-blue/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Create First Task
                </a>
                @endcan
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
