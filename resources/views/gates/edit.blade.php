<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('gates.index') }}" class="text-slate hover:text-navy">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-navy leading-tight">Edit {{ $gate->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-light-200 overflow-hidden">
            <form method="POST" action="{{ route('gates.update', $gate) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="block text-sm font-medium text-navy mb-2">Gate Name *</label>
                    <input type="text" id="name" name="name" required value="{{ old('name', $gate->name) }}"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-navy mb-2">Location</label>
                    <input type="text" id="location" name="location" value="{{ old('location', $gate->location) }}"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-navy mb-2">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">{{ old('description', $gate->description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy mb-2">Gate ID</label>
                    <p class="font-mono text-slate bg-light-100 px-3 py-2 rounded-lg">{{ $gate->gate_id }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ $gate->is_active ? 'checked' : '' }}
                        class="rounded border-light-200 text-sentinel-blue focus:ring-sentinel-blue">
                    <label for="is_active" class="text-sm font-medium text-navy">Active</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-light-200">
                    <a href="{{ route('gates.index') }}" class="px-4 py-2 text-slate hover:text-navy font-medium">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-sentinel-blue text-white font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
