<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-display font-bold text-2xl text-navy-900 leading-tight">
                {{ __('Profile Settings') }}
            </h2>
            <p class="text-slate-500 text-sm mt-1">Manage your account information and preferences.</p>
        </div>
    </x-slot>

    <div class="space-y-6 max-w-5xl mx-auto">
        <!-- Profile Information -->
        <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
            <div class="p-8">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Update Password -->
        <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
            <div class="p-8">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Delete Account -->
        <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
            <div class="p-8">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
