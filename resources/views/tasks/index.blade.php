<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-navy leading-tight">
                {{ __('Tasks') }}
            </h2>
            @can('create', App\Models\Task::class)
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-sentinel-blue text-white text-sm font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Task
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-light-200 p-4 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-slate mb-1">Status</label>
                    <select name="status" class="rounded-lg border-light-200 text-sm focus:ring-sentinel-blue focus:border-sentinel-blue">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-light-200 text-navy text-sm font-medium rounded-lg hover:bg-light-100 transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <!-- Tasks Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($tasks as $task)
            <div class="bg-white rounded-xl shadow-sm border border-light-200 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-navy">{{ $task->vendor->name }}</h3>
                            <p class="text-sm text-slate">{{ $task->vendor->email }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($task->status === 'active') bg-success/10 text-success
                            @elseif($task->status === 'completed') bg-slate/10 text-slate
                            @else bg-error/10 text-error @endif">
                            {{ ucfirst($task->status) }}
                        </span>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-slate">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>PIC: {{ $task->pic->name }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $task->start_time->format('M d, H:i') }} - {{ $task->end_time->format('M d, H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ $task->gates->count() }} gate(s)</span>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-3 bg-light-100 border-t border-light-200 flex items-center justify-between">
                    <a href="{{ route('tasks.show', $task) }}" class="text-sm text-sentinel-blue hover:text-sentinel-blue-dark font-medium">
                        View Details
                    </a>
                    @if($task->status === 'active' && auth()->user()->isDcfm())
                    <div class="flex gap-2">
                        <form action="{{ route('tasks.complete', $task) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-success hover:text-success/80 font-medium">Complete</button>
                        </form>
                        <form action="{{ route('tasks.revoke', $task) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-error hover:text-error/80 font-medium">Revoke</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-xl shadow-sm border border-light-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-light-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-navy">No tasks found</h3>
                <p class="mt-1 text-slate">Get started by creating a new task.</p>
                @can('create', App\Models\Task::class)
                <a href="{{ route('tasks.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-sentinel-blue text-white text-sm font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">
                    Create Task
                </a>
                @endcan
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
