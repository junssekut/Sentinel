<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tasks.index') }}" class="text-slate-500 hover:text-navy-900 hover:scale-110 transition-all duration-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-display font-bold text-2xl text-navy-900 leading-tight">
                    {{ __('Create Task') }}
                </h2>
                <p class="text-slate-500 text-sm mt-1">Assign access permissions to a vendor.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
            <form method="POST" action="{{ route('tasks.store') }}" class="p-8 space-y-6">
                @csrf

                <!-- Vendor Selection -->
                <div class="group">
                    <label for="vendor_id" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Vendor *</label>
                    <select id="vendor_id" name="vendor_id" required
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium">
                        <option value="">Select a vendor</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }} ({{ $vendor->email }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- PIC Selection -->
                <div class="group">
                    <label for="pic_id" class="block text-sm font-bold text-navy-900 mb-2 ml-1">PIC (Person in Charge) *</label>
                    <select id="pic_id" name="pic_id" required
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium">
                        <option value="">Select a PIC</option>
                        @foreach($pics as $pic)
                        <option value="{{ $pic->id }}" {{ old('pic_id') == $pic->id ? 'selected' : '' }}>
                            {{ $pic->name }} ({{ strtoupper($pic->role) }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Time Window -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="group">
                        <label for="start_time" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Start Time *</label>
                        <input type="datetime-local" id="start_time" name="start_time" required
                            value="{{ old('start_time') }}"
                            class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium">
                    </div>
                    <div class="group">
                        <label for="end_time" class="block text-sm font-bold text-navy-900 mb-2 ml-1">End Time *</label>
                        <input type="datetime-local" id="end_time" name="end_time" required
                            value="{{ old('end_time') }}"
                            class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium">
                    </div>
                </div>

                <!-- Allowed Gates -->
                <div>
                    <label class="block text-sm font-bold text-navy-900 mb-3 ml-1">Allowed Gates *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-5 bg-slate-50 rounded-2xl border border-gray-200">
                        @foreach($gates as $gate)
                        <label class="flex items-center gap-3 p-4 bg-white rounded-xl border-2 border-gray-200 cursor-pointer hover:border-sentinel-blue hover:shadow-md transition-all duration-200 group has-[:checked]:border-sentinel-blue has-[:checked]:bg-sentinel-blue/5">
                            <input type="checkbox" name="gate_ids[]" value="{{ $gate->id }}"
                                {{ in_array($gate->id, old('gate_ids', [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-sentinel-blue focus:ring-sentinel-blue w-5 h-5">
                            <div class="flex-1">
                                <p class="font-bold text-navy-900 text-sm group-hover:text-sentinel-blue transition-colors">{{ $gate->name }}</p>
                                <p class="text-xs text-slate-500 font-medium">{{ $gate->location ?? 'No location' }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @if($gates->isEmpty())
                    <p class="text-sm text-error mt-3 font-medium">⚠ No gates available. Please create gates first.</p>
                    @endif
                </div>

                <!-- Notes -->
                <div class="group">
                    <label for="notes" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="4"
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium resize-none"
                        placeholder="Add any additional notes about this task...">{{ old('notes') }}</textarea>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('tasks.index') }}" class="px-6 py-2.5 text-slate-600 hover:text-navy-900 font-bold hover:bg-slate-100 rounded-xl transition-all duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-2.5 bg-sentinel-gradient text-white font-bold rounded-xl hover:shadow-glow hover:scale-[1.02] transition-all duration-200 shadow-lg shadow-sentinel-blue/30">
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
