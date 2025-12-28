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

    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Gate Info Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                <div class="w-16 h-16 {{ $gate->is_active ? 'bg-success/10' : 'bg-slate/10' }} rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 {{ $gate->is_active ? 'text-success' : 'text-slate' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-navy text-lg text-center">{{ $gate->name }}</h3>
                <p class="text-slate text-sm text-center">{{ $gate->location ?? 'No location' }}</p>
                <div class="text-center mt-3 space-y-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $gate->is_active ? 'bg-success/10 text-success' : 'bg-slate/10 text-slate' }}">
                        {{ $gate->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($gate->door_id)
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ $gate->isIntegrated() ? ($gate->isOnline() ? 'bg-sentinel-blue/10 text-sentinel-blue' : 'bg-warning/10 text-warning') : 'bg-slate/10 text-slate' }}">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            {{ $gate->integration_status_label }}
                        </span>
                    </div>
                    @endif
                </div>
                @can('update', $gate)
                <div class="mt-6 pt-6 border-t border-light-200">
                    <a href="{{ route('gates.edit', $gate) }}" class="block text-center px-4 py-2 border border-light-200 text-navy text-sm font-medium rounded-lg hover:bg-light-100 transition-colors">Edit Gate</a>
                </div>
                @endcan
            </div>

            {{-- Details & Integration --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                    <h3 class="font-semibold text-navy mb-4">Details</h3>
                    <dl class="space-y-3">
                        <div><dt class="text-sm text-slate">Gate ID</dt><dd class="font-mono text-navy">{{ $gate->gate_id }}</dd></div>
                        <div><dt class="text-sm text-slate">Description</dt><dd class="text-navy">{{ $gate->description ?? 'No description' }}</dd></div>
                        <div><dt class="text-sm text-slate">Created</dt><dd class="text-navy">{{ $gate->created_at->format('M d, Y H:i') }}</dd></div>
                    </dl>
                </div>

                {{-- Door Integration Info --}}
                @if($gate->door_id)
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                    <h3 class="font-semibold text-navy mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Door Integration
                    </h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-slate">Door ID</dt>
                            <dd class="font-mono text-navy">{{ $gate->door_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-slate">IP Address</dt>
                            <dd class="font-mono text-navy">{{ $gate->door_ip_address ?? 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-slate">Status</dt>
                            <dd class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $gate->isIntegrated() && $gate->isOnline() ? 'bg-success animate-pulse' : ($gate->isIntegrated() ? 'bg-warning' : 'bg-slate-300') }}"></span>
                                <span class="text-navy">{{ $gate->integration_status_label }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-slate">Last Heartbeat</dt>
                            <dd class="text-navy">{{ $gate->last_heartbeat_at ? $gate->last_heartbeat_at->diffForHumans() : 'Never' }}</dd>
                        </div>
                    </dl>
                </div>
                @endif

                {{-- Live Access Logs --}}
                <div class="bg-white rounded-xl shadow-sm border border-light-200" id="access-logs-container">
                    <div class="px-6 py-4 border-b border-light-200 flex items-center justify-between">
                        <h3 class="font-semibold text-navy flex items-center gap-2">
                            <svg class="w-5 h-5 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Live Access Logs
                        </h3>
                        <div class="flex items-center gap-2">
                            <span id="live-indicator" class="w-2 h-2 rounded-full bg-success animate-pulse"></span>
                            <span class="text-xs text-slate-500">Live</span>
                        </div>
                    </div>
                    <div id="access-logs" class="divide-y divide-light-200 max-h-96 overflow-y-auto">
                        {{-- Initial logs from server --}}
                        @forelse($gate->accessLogs()->with(['vendor', 'pic'])->latest()->take(20)->get() as $log)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-light-100/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    {{ $log->event_type === 'entry' ? 'bg-success/10 text-success' : ($log->event_type === 'exit' ? 'bg-sentinel-blue/10 text-sentinel-blue' : 'bg-error/10 text-error') }}">
                                    @if($log->event_type === 'entry')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                    @elseif($log->event_type === 'exit')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-navy">
                                        {{ ucfirst($log->event_type) }}
                                        @if($log->vendor)
                                        — {{ $log->vendor->name }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        @if($log->pic) PIC: {{ $log->pic->name }} @endif
                                        @if($log->reason) • {{ $log->reason }} @endif
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        @empty
                        <div class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-slate-500">No access logs yet</p>
                            <p class="text-xs text-slate-400 mt-1">Logs will appear here when doors are accessed</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Tasks --}}
                @if($gate->tasks->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-light-200">
                    <div class="px-6 py-4 border-b border-light-200"><h3 class="font-semibold text-navy">Recent Tasks</h3></div>
                    <div class="divide-y divide-light-200">
                        @foreach($gate->tasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-4 hover:bg-light-100/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-navy">{{ $task->vendors->isNotEmpty() ? $task->vendors->pluck('name')->join(', ') : 'No vendors' }}</p>
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

    {{-- Polling Script for Live Access Logs --}}
    @if($gate->door_id)
    @push('scripts')
    <script>
        (function() {
            const gateId = {{ $gate->id }};
            const logsContainer = document.getElementById('access-logs');
            const liveIndicator = document.getElementById('live-indicator');
            let lastTimestamp = null;
            
            async function fetchLogs() {
                try {
                    const url = `/api/gates/${gateId}/access-logs` + (lastTimestamp ? `?since=${lastTimestamp}` : '?limit=20');
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) return;
                    
                    const data = await response.json();
                    lastTimestamp = data.timestamp;
                    
                    // Update live indicator based on gate status
                    if (data.is_online) {
                        liveIndicator.classList.remove('bg-warning', 'bg-slate-300');
                        liveIndicator.classList.add('bg-success', 'animate-pulse');
                    } else {
                        liveIndicator.classList.remove('bg-success', 'animate-pulse');
                        liveIndicator.classList.add('bg-warning');
                    }
                    
                    // Prepend new logs
                    if (data.logs && data.logs.length > 0) {
                        data.logs.reverse().forEach(log => {
                            const logHtml = createLogEntry(log);
                            logsContainer.insertAdjacentHTML('afterbegin', logHtml);
                        });
                    }
                } catch (error) {
                    console.error('Failed to fetch logs:', error);
                    liveIndicator.classList.remove('bg-success', 'animate-pulse');
                    liveIndicator.classList.add('bg-slate-300');
                }
            }
            
            function createLogEntry(log) {
                const iconClass = log.event_type === 'entry' ? 'bg-success/10 text-success' : 
                                  (log.event_type === 'exit' ? 'bg-sentinel-blue/10 text-sentinel-blue' : 'bg-error/10 text-error');
                const icon = log.event_type === 'entry' ? 
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>' :
                    (log.event_type === 'exit' ? 
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>' :
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>');
                
                return `
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-light-100/50 transition-colors animate-pulse-once">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center ${iconClass}">
                                ${icon}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-navy">
                                    ${log.event_type.charAt(0).toUpperCase() + log.event_type.slice(1)}
                                    ${log.vendor ? '— ' + log.vendor : ''}
                                </p>
                                <p class="text-xs text-slate-500">
                                    ${log.pic ? 'PIC: ' + log.pic : ''}
                                    ${log.reason ? ' • ' + log.reason : ''}
                                </p>
                            </div>
                        </div>
                        <span class="text-xs text-slate-400">${log.created_at_human}</span>
                    </div>
                `;
            }
            
            // Poll every 5 seconds
            setInterval(fetchLogs, 5000);
        })();
    </script>
    @endpush
    @endif
</x-app-layout>
