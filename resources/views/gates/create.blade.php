<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('gates.index') }}" class="text-slate-500 hover:text-navy-900 hover:scale-110 transition-all duration-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-display font-bold text-2xl text-navy-900 leading-tight">{{ __('Add Gate') }}</h2>
                <p class="text-slate-500 text-sm mt-1">Register a new access point to the system.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
            <form method="POST" action="{{ route('gates.store') }}" class="p-8 space-y-6">
                @csrf
                
                <!-- Gate Name -->
                <div class="group">
                    <label for="name" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Gate Name *</label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}"
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium"
                        placeholder="e.g., Main Entrance">
                </div>
                
                <!-- Location -->
                <div class="group">
                    <label for="location" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Location</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}"
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium"
                        placeholder="e.g., Building A, Floor 1">
                </div>
                
                <!-- Description -->
                <div class="group">
                    <label for="description" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Description</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium resize-none"
                        placeholder="Optional description of this access point...">{{ old('description') }}</textarea>
                </div>
                
                <!-- Active Status -->
                <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-gray-200">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked
                        class="rounded border-gray-300 text-sentinel-blue focus:ring-sentinel-blue w-5 h-5">
                    <label for="is_active" class="text-sm font-bold text-navy-900">
                        Mark gate as active
                        <span class="block text-xs text-slate-500 font-normal mt-0.5">Active gates can be assigned to tasks immediately.</span>
                    </label>
                </div>
                
                <!-- Submit -->
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('gates.index') }}" class="px-6 py-2.5 text-slate-600 hover:text-navy-900 font-bold hover:bg-slate-100 rounded-xl transition-all duration-200">Cancel</a>
                    <button type="submit" class="px-8 py-2.5 bg-sentinel-gradient text-white font-bold rounded-xl hover:shadow-glow hover:scale-[1.02] transition-all duration-200 shadow-lg shadow-sentinel-blue/30">
                        Add Gate
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
