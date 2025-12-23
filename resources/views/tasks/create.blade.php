<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tasks.index') }}" class="text-slate hover:text-navy">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-navy leading-tight">
                {{ __('Create Task') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-light-200 overflow-hidden">
            <form method="POST" action="{{ route('tasks.store') }}" class="p-6 space-y-6">
                @csrf

                <!-- Vendor Selection -->
                <div>
                    <label for="vendor_id" class="block text-sm font-medium text-navy mb-2">Vendor *</label>
                    <select id="vendor_id" name="vendor_id" required
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                        <option value="">Select a vendor</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }} ({{ $vendor->email }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- PIC Selection -->
                <div>
                    <label for="pic_id" class="block text-sm font-medium text-navy mb-2">PIC (Person in Charge) *</label>
                    <select id="pic_id" name="pic_id" required
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                        <option value="">Select a PIC</option>
                        @foreach($pics as $pic)
                        <option value="{{ $pic->id }}" {{ old('pic_id') == $pic->id ? 'selected' : '' }}>
                            {{ $pic->name }} ({{ strtoupper($pic->role) }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Time Window -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-navy mb-2">Start Time *</label>
                        <input type="datetime-local" id="start_time" name="start_time" required
                            value="{{ old('start_time') }}"
                            class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-navy mb-2">End Time *</label>
                        <input type="datetime-local" id="end_time" name="end_time" required
                            value="{{ old('end_time') }}"
                            class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                    </div>
                </div>

                <!-- Allowed Gates -->
                <div>
                    <label class="block text-sm font-medium text-navy mb-2">Allowed Gates *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 p-4 bg-light-100 rounded-lg">
                        @foreach($gates as $gate)
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-light-200 cursor-pointer hover:border-sentinel-blue transition-colors">
                            <input type="checkbox" name="gate_ids[]" value="{{ $gate->id }}"
                                {{ in_array($gate->id, old('gate_ids', [])) ? 'checked' : '' }}
                                class="rounded border-light-200 text-sentinel-blue focus:ring-sentinel-blue">
                            <div>
                                <p class="font-medium text-navy text-sm">{{ $gate->name }}</p>
                                <p class="text-xs text-slate">{{ $gate->location ?? 'No location' }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @if($gates->isEmpty())
                    <p class="text-sm text-error mt-2">No gates available. Please create gates first.</p>
                    @endif
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-navy mb-2">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue"
                        placeholder="Optional notes about this task...">{{ old('notes') }}</textarea>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3 pt-4 border-t border-light-200">
                    <a href="{{ route('tasks.index') }}" class="px-4 py-2 text-slate hover:text-navy font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-sentinel-blue text-white font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
