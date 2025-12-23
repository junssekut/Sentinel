<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="text-slate hover:text-navy">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-navy leading-tight">
                {{ $user->name }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6 text-center">
                    <div class="w-24 h-24 bg-sentinel-blue/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-sentinel-blue font-bold text-3xl">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <h3 class="font-semibold text-navy text-lg">{{ $user->name }}</h3>
                    <p class="text-slate text-sm">{{ $user->email }}</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-3
                        @if($user->role === 'dcfm') bg-sentinel-blue/10 text-sentinel-blue
                        @elseif($user->role === 'soc') bg-navy/10 text-navy
                        @else bg-slate/10 text-slate @endif">
                        {{ strtoupper($user->role) }}
                    </span>

                    @can('update', $user)
                    <div class="mt-6 pt-6 border-t border-light-200">
                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center justify-center w-full px-4 py-2 border border-light-200 text-navy text-sm font-medium rounded-lg hover:bg-light-100 transition-colors">
                            Edit Profile
                        </a>
                    </div>
                    @endcan
                </div>
            </div>

            <!-- Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-light-200 p-6">
                    <h3 class="font-semibold text-navy mb-4">Account Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-slate">Face ID</dt>
                            <dd class="font-mono text-navy">{{ $user->face_id ?? 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-slate">Email Verified</dt>
                            <dd class="text-navy">{{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y') : 'Not verified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-slate">Created At</dt>
                            <dd class="text-navy">{{ $user->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-slate">Last Updated</dt>
                            <dd class="text-navy">{{ $user->updated_at->format('M d, Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                @if($user->isVendor() && isset($user->vendorTasks))
                <div class="bg-white rounded-xl shadow-sm border border-light-200">
                    <div class="px-6 py-4 border-b border-light-200">
                        <h3 class="font-semibold text-navy">Recent Tasks</h3>
                    </div>
                    <div class="divide-y divide-light-200">
                        @forelse($user->vendorTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-4 hover:bg-light-100/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-navy">PIC: {{ $task->pic->name ?? 'N/A' }}</p>
                                    <p class="text-sm text-slate">{{ $task->start_time->format('M d, H:i') }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($task->status === 'active') bg-success/10 text-success
                                    @elseif($task->status === 'completed') bg-slate/10 text-slate
                                    @else bg-error/10 text-error @endif">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </div>
                        </a>
                        @empty
                        <div class="px-6 py-8 text-center text-slate">No tasks found.</div>
                        @endforelse
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
