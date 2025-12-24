<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-display font-bold text-2xl text-navy-900 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <p class="text-slate-500 text-sm mt-1">Overview of your verification status and tasks.</p>
        </div>
    </x-slot>

    <!-- Bento Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Active Tasks Card (Double Width) -->
        <div class="col-span-1 md:col-span-2 bg-white rounded-2xl shadow-bento hover:shadow-bento-hover transition-all duration-300 overflow-hidden relative group border border-white/60">
            <div class="absolute right-0 top-0 w-32 h-32 bg-sentinel-blue/5 rounded-bl-full group-hover:bg-sentinel-blue/10 transition-colors"></div>
            <div class="p-6 relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-sentinel-blue/10 flex items-center justify-center text-sentinel-blue">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Real-time</span>
                </div>
                <h3 class="text-4xl font-display font-bold text-navy-900 mb-1">{{ $stats['active_tasks'] ?? 0 }}</h3>
                <p class="text-slate-500 font-medium">Active Tasks</p>
            </div>
            <!-- Decorative Chart Line -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-sentinel-blue to-transparent transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
        </div>

        @if(auth()->user()->canViewAllTasks())
        <!-- Total Vendors -->
        <div class="bg-white rounded-2xl shadow-bento hover:shadow-bento-hover transition-all duration-300 p-6 border border-white/60 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-navy/5 flex items-center justify-center text-navy-700 group-hover:bg-navy/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <h3 class="text-3xl font-display font-bold text-navy-900 mb-1">{{ $stats['total_vendors'] ?? 0 }}</h3>
            <p class="text-slate-500 font-medium">Total Vendors</p>
        </div>

        <!-- Today's Access -->
        <div class="bg-gradient-to-br from-sentinel-blue to-sentinel-blue-dark rounded-2xl shadow-bento hover:shadow-glow transition-all duration-300 p-6 text-white relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2 group-hover:bg-white/20 transition-colors"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white backdrop-blur-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </div>
                </div>
                <h3 class="text-3xl font-display font-bold text-white mb-1">{{ $stats['today_access_attempts'] ?? 0 }}</h3>
                <p class="text-blue-100 font-medium">Access Attempts</p>
            </div>
        </div>
        @else
        <!-- Completed Tasks (Vendor View) -->
        <div class="bg-white rounded-2xl shadow-bento hover:shadow-bento-hover transition-all duration-300 p-6 border border-white/60">
            <div class="flex items-center justify-between mb-4">
                 <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center text-success">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                 </div>
            </div>
            <h3 class="text-3xl font-display font-bold text-navy-900 mb-1">{{ $stats['completed_tasks'] ?? 0 }}</h3>
            <p class="text-slate-500 font-medium">Tasks Completed</p>
        </div>
        @endif
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Recent Activity Feed -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-display font-bold text-lg text-navy-900">Recent Tasks</h3>
                    <a href="{{ route('tasks.index') }}" class="text-sm font-medium text-sentinel-blue hover:text-sentinel-blue-dark hover:underline transition-colors decoration-2 underline-offset-2">View All</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($recentTasks as $task)
                    <div class="px-6 py-4 hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold border-2 border-white shadow-sm group-hover:scale-110 transition-transform">
                                    {{ substr($task->vendor->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-navy-900">{{ $task->vendor->name }}</p>
                                    <p class="text-sm text-slate-500 font-medium">PIC: {{ $task->pic->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide
                                    @if($task->status === 'active') bg-success/10 text-success border border-success/20
                                    @elseif($task->status === 'completed') bg-slate-100 text-slate-600 border border-slate-200
                                    @else bg-error/10 text-error border border-error/20 @endif">
                                    {{ $task->status }}
                                </span>
                                <p class="text-xs text-slate-400 mt-1 font-mono">
                                    {{ $task->start_time->format('H:i') }} - {{ $task->end_time->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <p class="text-navy-900 font-medium">No recent tasks</p>
                        <p class="text-slate-500 text-sm">Tasks will appear here once created.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-6">
            @can('create', App\Models\Task::class)
            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-bento border border-white/60 p-6">
                <h3 class="font-display font-bold text-navy-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('tasks.create') }}" class="group flex items-center justify-between p-4 rounded-xl bg-sentinel-blue text-white hover:shadow-lg hover:shadow-sentinel-blue/30 transition-all duration-300 transform hover:-translate-y-1">
                        <span class="font-bold flex items-center gap-2">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                             Assign Task
                        </span>
                        <svg class="w-5 h-5 opacity-70 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('users.create') }}" class="group flex items-center justify-between p-4 rounded-xl border border-slate-200 hover:border-sentinel-blue hover:text-sentinel-blue transition-all duration-300 bg-white">
                        <span class="font-medium flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            Register User
                        </span>
                    </a>
                </div>
            </div>
            @endcan

            @if(auth()->user()->canViewAllTasks() && $recentLogs->count() > 0)
            <!-- Recent Access Logs Widget -->
            <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-slate-50/50">
                    <h3 class="font-display font-bold text-navy-900">Live Access Logs</h3>
                </div>
                <div class="max-h-80 overflow-y-auto custom-scrollbar">
                    @foreach($recentLogs as $log)
                    <div class="px-5 py-3 border-b border-gray-50 flex items-center gap-3 hover:bg-slate-50 transition-colors">
                        <div class="w-2 h-2 rounded-full {{ $log->success ? 'bg-success shadow-[0_0_8px_rgba(47,191,113,0.5)]' : 'bg-error shadow-[0_0_8px_rgba(230,57,70,0.5)]' }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-navy-900 truncate">
                                {{ $log->details['gate_id'] ?? 'Unknown Gate' }}
                            </p>
                            <div class="flex items-center justify-between mt-0.5">
                                <span class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</span>
                                <span class="text-[10px] uppercase font-bold {{ $log->success ? 'text-success' : 'text-error' }}">
                                    {{ $log->success ? 'GRANTED' : 'DENIED' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
