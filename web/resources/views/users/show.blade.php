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
                    <!-- Face Image Preview -->
                    @if($user->face_image)
                    <div class="w-32 h-32 rounded-full overflow-hidden mx-auto mb-4 border-4 {{ $user->face_embedding ? 'border-success/40' : 'border-warning/40' }}">
                        <img src="{{ $user->face_image }}" alt="{{ $user->name }}'s face" class="w-full h-full object-cover">
                    </div>
                    @if($user->face_embedding)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-success/10 text-success mb-2">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        Approved
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-warning/10 text-warning mb-2">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                        Pending Approval
                    </span>
                    @endif
                    @else
                    <div class="w-32 h-32 bg-slate/10 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-light-200">
                        <span class="text-slate font-bold text-4xl">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate/10 text-slate mb-2">
                        No Face Enrolled
                    </span>
                    @endif
                    
                    <h3 class="font-semibold text-navy text-lg">{{ $user->name }}</h3>
                    <p class="text-slate text-sm">{{ $user->email }}</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-3
                        @if($user->role === 'dcfm') bg-sentinel-blue/10 text-sentinel-blue
                        @elseif($user->role === 'soc') bg-navy/10 text-navy
                        @elseif($user->role === 'pic') bg-success/10 text-success
                        @else bg-slate/10 text-slate @endif">
                        {{ strtoupper($user->role) }}
                    </span>

                    @can('update', $user)
                    <div class="mt-6 pt-6 border-t border-light-200 space-y-3">
                        <!-- Approve/Reject Buttons -->
                        @if($user->face_image && !$user->face_embedding)
                        <form action="{{ route('users.approve', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center w-full px-4 py-2 bg-success text-white text-sm font-medium rounded-lg hover:bg-success/90 transition-colors gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Approve Face
                            </button>
                        </form>
                        @elseif($user->face_embedding)
                        <form action="{{ route('users.reject', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center w-full px-4 py-2 bg-error/10 text-error text-sm font-medium rounded-lg hover:bg-error/20 transition-colors gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Revoke Approval
                            </button>
                        </form>
                        @endif
                        
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
                            <dt class="text-sm text-slate">User ID</dt>
                            <dd class="font-mono text-navy">#{{ $user->id }}</dd>
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
