<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-2xl text-navy-900 leading-tight">
                    {{ __('Users') }}
                </h2>
                <p class="text-slate-500 text-sm mt-1">Manage system users and permissions.</p>
            </div>
            @can('create', App\Models\User::class)
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sentinel-gradient text-white text-sm font-bold rounded-xl hover:shadow-glow hover:scale-[1.02] transition-all duration-200 shadow-lg shadow-sentinel-blue/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New User
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-br from-slate-50 to-slate-100/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-navy-900 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-navy-900 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-navy-900 uppercase tracking-wider">Face Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-navy-900 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-navy-900 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                @if($user->face_image)
                                <div class="w-11 h-11 rounded-full overflow-hidden border-2 border-sentinel-blue/30 transform group-hover:scale-110 transition-transform duration-200">
                                    <img src="{{ $user->face_image }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                </div>
                                @else
                                <div class="w-11 h-11 bg-sentinel-gradient rounded-full flex items-center justify-center shadow-md transform group-hover:scale-110 transition-transform duration-200">
                                    <span class="text-white font-display font-bold text-lg">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                @endif
                                <div>
                                    <div class="text-sm font-bold text-navy-900">{{ $user->name }}</div>
                                    <div class="text-sm text-slate-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                                @if($user->role === 'dcfm') bg-sentinel-blue/10 text-sentinel-blue border border-sentinel-blue/20
                                @elseif($user->role === 'soc') bg-navy-800/10 text-navy-800 border border-navy/20
                                @elseif($user->role === 'pic') bg-success/10 text-success border border-success/20
                                @else bg-slate-200 text-slate-600 border border-slate-300 @endif">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->face_embedding)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-success/10 text-success border border-success/20">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                Enrolled
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-warning/10 text-warning border border-warning/20">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                Not Enrolled
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('users.show', $user) }}" class="text-sentinel-blue hover:text-sentinel-blue-dark font-bold hover:underline decoration-2 underline-offset-2">View</a>
                                @can('update', $user)
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('users.edit', $user) }}" class="text-slate-600 hover:text-navy-900 font-bold hover:underline decoration-2 underline-offset-2">Edit</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="w-16 h-16 bg-sentinel-blue/5 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-sentinel-blue/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <p class="text-slate-500 font-medium">No users found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-slate-50/50">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
