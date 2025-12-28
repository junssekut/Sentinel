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

                <!-- Task Title -->
                <div class="group">
                    <label for="title" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Task Title *</label>
                    <input type="text" id="title" name="title" required
                        value="{{ old('title') }}"
                        placeholder="e.g., Monthly Equipment Maintenance"
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium">
                </div>

                <!-- Vendors Selection (Addable List) -->
                <div class="group">
                    <label class="block text-sm font-bold text-navy-900 mb-2 ml-1">Vendors *</label>
                    
                    <!-- Vendor Selector -->
                    <div class="flex gap-3 mb-4">
                        <select id="vendor-select" class="flex-1 rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium">
                            <option value="" data-name="" data-email="" data-face="">Select a vendor to add</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" data-name="{{ $vendor->name }}" data-email="{{ $vendor->email }}" data-face="{{ $vendor->face_image }}">
                                {{ $vendor->name }} ({{ $vendor->email }})
                            </option>
                            @endforeach
                        </select>
                        <button type="button" id="add-vendor-btn" class="px-5 py-3 bg-sentinel-blue text-white font-bold rounded-xl hover:bg-sentinel-blue/90 transition-all duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Add
                        </button>
                    </div>

                    <!-- Added Vendors List -->
                    <div id="vendors-list" class="grid grid-cols-1 md:grid-cols-2 gap-3 min-h-[60px]">
                        <p id="no-vendors-msg" class="text-slate-400 text-sm col-span-full p-4 bg-slate-50 rounded-xl border border-gray-200 border-dashed">No vendors added yet. Select vendors from above.</p>
                    </div>
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

    @push('scripts')
    <script>
    (function() {
        const vendorSelect = document.getElementById('vendor-select');
        const addBtn = document.getElementById('add-vendor-btn');
        const vendorsList = document.getElementById('vendors-list');
        const noVendorsMsg = document.getElementById('no-vendors-msg');
        const addedVendors = new Set();

        function updateNoVendorsMessage() {
            noVendorsMsg.style.display = addedVendors.size === 0 ? 'block' : 'none';
        }

        function createVendorCard(id, name, email, faceImage) {
            // Get initials for avatar fallback
            const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            
            // Determine avatar content - use image if available, otherwise initials
            let avatarContent;
            if (faceImage) {
                // Check if it already has data URI prefix
                const imgSrc = faceImage.startsWith('data:') ? faceImage : `data:image/jpeg;base64,${faceImage}`;
                avatarContent = `<img src="${imgSrc}" alt="${name}" class="w-12 h-12 rounded-full object-cover flex-shrink-0">`;
            } else {
                avatarContent = `<div class="w-12 h-12 rounded-full bg-sentinel-blue/10 flex items-center justify-center text-sentinel-blue font-bold text-lg flex-shrink-0">${initials}</div>`;
            }
            
            const card = document.createElement('div');
            card.className = 'flex items-center gap-3 p-4 bg-white rounded-xl border-2 border-sentinel-blue/30 shadow-sm hover:shadow-md transition-all duration-200';
            card.dataset.vendorId = id;
            card.innerHTML = `
                <input type="hidden" name="vendor_ids[]" value="${id}">
                ${avatarContent}
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-navy-900 truncate">${name}</p>
                    <p class="text-sm text-slate-500 truncate">${email}</p>
                </div>
                <button type="button" class="remove-vendor-btn p-2 hover:bg-error/10 rounded-lg transition-colors text-slate-400 hover:text-error flex-shrink-0" title="Remove vendor">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            return card;
        }

        addBtn.addEventListener('click', function() {
            const selected = vendorSelect.options[vendorSelect.selectedIndex];
            if (!selected.value) return;

            const id = selected.value;
            const name = selected.dataset.name;
            const email = selected.dataset.email;
            const faceImage = selected.dataset.face;

            if (addedVendors.has(id)) {
                // Already added
                return;
            }

            addedVendors.add(id);
            vendorsList.appendChild(createVendorCard(id, name, email, faceImage));
            
            // Disable option in dropdown
            selected.disabled = true;
            vendorSelect.value = '';
            
            updateNoVendorsMessage();
        });

        vendorsList.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-vendor-btn');
            if (!removeBtn) return;

            const chip = removeBtn.closest('[data-vendor-id]');
            const id = chip.dataset.vendorId;

            addedVendors.delete(id);
            chip.remove();

            // Re-enable option in dropdown
            const option = vendorSelect.querySelector(`option[value="${id}"]`);
            if (option) option.disabled = false;

            updateNoVendorsMessage();
        });
    })();
    </script>
    @endpush
</x-app-layout>
