<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-navy leading-tight">
                {{ __('Users') }}
            </h2>
            @can('create', App\Models\User::class)
            <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-sentinel-blue text-white text-sm font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New User
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-light-200 overflow-hidden">
            <table class="min-w-full divide-y divide-light-200">
                <thead class="bg-light-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate uppercase tracking-wider">Face ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-light-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-light-100/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-sentinel-blue/10 rounded-full flex items-center justify-center">
                                    <span class="text-sentinel-blue font-semibold">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-navy">{{ $user->name }}</div>
                                    <div class="text-sm text-slate">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($user->role === 'dcfm') bg-sentinel-blue/10 text-sentinel-blue
                                @elseif($user->role === 'soc') bg-navy/10 text-navy
                                @else bg-slate/10 text-slate @endif">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->face_id)
                            <span class="text-sm font-mono text-slate">{{ Str::limit($user->face_id, 12) }}</span>
                            @else
                            <span class="text-sm text-error">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('users.show', $user) }}" class="text-sentinel-blue hover:text-sentinel-blue-dark mr-3">View</a>
                            @can('update', $user)
                            <a href="{{ route('users.edit', $user) }}" class="text-slate hover:text-navy">Edit</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate">
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($users->hasPages())
            <div class="px-6 py-4 border-t border-light-200">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
