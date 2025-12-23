<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-navy leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <span class="text-sm text-slate">
                Welcome, {{ auth()->user()->name }}
                <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium 
                    @if(auth()->user()->role === 'dcfm') bg-sentinel-blue text-white
                    @elseif(auth()->user()->role === 'soc') bg-navy text-white
                    @else bg-slate/20 text-slate @endif">
                    {{ strtoupper(auth()->user()->role) }}
                </span>
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Grid - Bento Style -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Active Tasks -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-light-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate">Active Tasks</p>
                        <p class="text-3xl font-bold text-navy mt-1">{{ $stats['active_tasks'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-sentinel-blue/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            @if(auth()->user()->canViewAllTasks())
            <!-- Total Vendors -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-light-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate">Total Vendors</p>
                        <p class="text-3xl font-bold text-navy mt-1">{{ $stats['total_vendors'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-navy/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Gates -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-light-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate">Active Gates</p>
                        <p class="text-3xl font-bold text-navy mt-1">{{ $stats['total_gates'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Today's Access Attempts -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-light-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate">Today's Access</p>
                        <p class="text-3xl font-bold text-navy mt-1">{{ $stats['today_access_attempts'] ?? 0 }}</p>
                        @if(($stats['today_denied'] ?? 0) > 0)
                            <p class="text-xs text-error mt-1">{{ $stats['today_denied'] }} denied</p>
                        @endif
                    </div>
                    <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            @else
            <!-- Completed Tasks (for vendors) -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-light-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate">Completed Tasks</p>
                        <p class="text-3xl font-bold text-navy mt-1">{{ $stats['completed_tasks'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Tasks -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-light-200">
                <div class="px-6 py-4 border-b border-light-200 flex items-center justify-between">
                    <h3 class="font-semibold text-navy">Recent Tasks</h3>
                    <a href="{{ route('tasks.index') }}" class="text-sm text-sentinel-blue hover:text-sentinel-blue-dark">
                        View all →
                    </a>
                </div>
                <div class="divide-y divide-light-200">
                    @forelse($recentTasks as $task)
                    <div class="px-6 py-4 hover:bg-light-100/50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-navy">{{ $task->vendor->name }}</p>
                                <p class="text-sm text-slate">PIC: {{ $task->pic->name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($task->status === 'active') bg-success/10 text-success
                                    @elseif($task->status === 'completed') bg-slate/10 text-slate
                                    @else bg-error/10 text-error @endif">
                                    {{ ucfirst($task->status) }}
                                </span>
                                <p class="text-xs text-slate mt-1">
                                    {{ $task->start_time->format('M d, H:i') }} - {{ $task->end_time->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-slate">
                        <svg class="w-12 h-12 mx-auto text-light-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="mt-2">No tasks found</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Actions / Recent Activity -->
            <div class="space-y-6">
                @can('create', App\Models\Task::class)
                <!-- Quick Actions for DCFM -->
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                    <h3 class="font-semibold text-navy mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('tasks.create') }}" class="flex items-center gap-3 p-3 rounded-lg bg-sentinel-blue text-white hover:bg-sentinel-blue-dark transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Assign New Task
                        </a>
                        <a href="{{ route('users.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-light-200 text-navy hover:bg-light-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Register User
                        </a>
                        <a href="{{ route('gates.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-light-200 text-navy hover:bg-light-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Add Gate
                        </a>
                    </div>
                </div>
                @endcan

                @if(auth()->user()->canViewAllTasks() && $recentLogs->count() > 0)
                <!-- Recent Access Logs -->
                <div class="bg-white rounded-xl shadow-sm border border-light-200">
                    <div class="px-6 py-4 border-b border-light-200">
                        <h3 class="font-semibold text-navy">Recent Access Logs</h3>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        @foreach($recentLogs as $log)
                        <div class="px-4 py-3 border-b border-light-100 last:border-0 flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $log->success ? 'bg-success' : 'bg-error' }}"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-navy truncate">
                                    {{ $log->details['gate_id'] ?? 'Unknown Gate' }}
                                </p>
                                <p class="text-xs text-slate">{{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
