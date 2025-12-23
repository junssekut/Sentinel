<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.show', $user) }}" class="text-slate hover:text-navy">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-navy leading-tight">
                Edit {{ $user->name }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-light-200 overflow-hidden">
            <form method="POST" action="{{ route('users.update', $user) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-navy mb-2">Full Name *</label>
                    <input type="text" id="name" name="name" required
                        value="{{ old('name', $user->name) }}"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-navy mb-2">Email *</label>
                    <input type="email" id="email" name="email" required
                        value="{{ old('email', $user->email) }}"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-navy mb-2">New Password</label>
                    <input type="password" id="password" name="password"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue"
                        placeholder="Leave blank to keep current">
                </div>

                @if(auth()->user()->isDcfm())
                <div>
                    <label for="role" class="block text-sm font-medium text-navy mb-2">Role</label>
                    <select id="role" name="role"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                        <option value="vendor" {{ $user->role === 'vendor' ? 'selected' : '' }}>Vendor</option>
                        <option value="dcfm" {{ $user->role === 'dcfm' ? 'selected' : '' }}>DCFM</option>
                        <option value="soc" {{ $user->role === 'soc' ? 'selected' : '' }}>SOC</option>
                    </select>
                </div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-light-200">
                    <a href="{{ route('users.show', $user) }}" class="px-4 py-2 text-slate hover:text-navy font-medium">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-sentinel-blue text-white font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
