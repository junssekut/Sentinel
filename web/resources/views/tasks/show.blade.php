<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tasks.index') }}" class="text-slate hover:text-navy">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-navy leading-tight">
                {{ __('Task Details') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($task->status === 'active') bg-success/10 text-success
                            @elseif($task->status === 'completed') bg-slate/10 text-slate
                            @else bg-error/10 text-error @endif">
                            {{ ucfirst($task->status) }}
                        </span>
                        @if($task->status === 'active' && auth()->user()->isDcfm())
                        <div class="flex gap-2">
                            <form action="{{ route('tasks.complete', $task) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-success text-white text-sm font-medium rounded-lg hover:bg-success/90 transition-colors">
                                    Complete
                                </button>
                            </form>
                            <form action="{{ route('tasks.revoke', $task) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-error text-white text-sm font-medium rounded-lg hover:bg-error/90 transition-colors">
                                    Revoke
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>

                    <!-- Vendors Info -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-slate mb-2">Vendors</h3>
                        <div class="space-y-3">
                            @forelse($task->vendors as $vendor)
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-sentinel-blue/10 rounded-full flex items-center justify-center">
                                    <span class="text-sentinel-blue font-semibold text-lg">{{ substr($vendor->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-navy">{{ $vendor->name }}</p>
                                    <p class="text-sm text-slate">{{ $vendor->email }}</p>
                                </div>
                            </div>
                            @empty
                            <p class="text-slate text-sm">No vendors assigned</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- PIC Info -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-slate mb-2">PIC (Person in Charge)</h3>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-navy/10 rounded-full flex items-center justify-center">
                                <span class="text-navy font-semibold text-lg">{{ substr($task->pic->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-navy">{{ $task->pic->name }}</p>
                                <p class="text-sm text-slate">{{ strtoupper($task->pic->role) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Time Window -->
                    <div>
                        <h3 class="text-sm font-medium text-slate mb-2">Time Window</h3>
                        <div class="flex items-center gap-4 text-navy">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ $task->start_time->format('M d, Y H:i') }}</span>
                            </div>
                            <span class="text-slate">→</span>
                            <span>{{ $task->end_time->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                @if($task->notes)
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                    <h3 class="text-sm font-medium text-slate mb-2">Notes</h3>
                    <p class="text-navy">{{ $task->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Allowed Gates -->
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                    <h3 class="font-semibold text-navy mb-4">Allowed Gates</h3>
                    <div class="space-y-3">
                        @foreach($task->gates as $gate)
                        <div class="flex items-center gap-3 p-3 bg-light-100 rounded-lg">
                            <div class="w-8 h-8 bg-success/10 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-navy text-sm">{{ $gate->name }}</p>
                                <p class="text-xs text-slate">{{ $gate->location ?? 'No location' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                    <h3 class="font-semibold text-navy mb-4">Information</h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-slate">Task ID</dt>
                            <dd class="font-mono text-navy">#{{ $task->id }}</dd>
                        </div>
                        @if($task->creator)
                        <div>
                            <dt class="text-slate">Created By</dt>
                            <dd class="text-navy">{{ $task->creator->name }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-slate">Created At</dt>
                            <dd class="text-navy">{{ $task->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
