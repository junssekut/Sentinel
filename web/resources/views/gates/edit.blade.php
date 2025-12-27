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

                {{-- Door Integration Settings (SOC only) --}}
                @if(auth()->user()->isSoc())
                <div class="pt-6 border-t border-light-200">
                    <h3 class="text-lg font-semibold text-navy mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Door Integration Settings
                    </h3>
                    <p class="text-sm text-slate-500 mb-4">Configure the physical door lock device for this gate.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="door_id" class="block text-sm font-medium text-navy mb-2">
                                Door ID (DEVICE_ID)
                                <span class="text-slate-400 font-normal">— matches client .env DEVICE_ID</span>
                            </label>
                            <input type="text" id="door_id" name="door_id" 
                                value="{{ old('door_id', $gate->door_id) }}"
                                placeholder="e.g., mac-client-1"
                                class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue font-mono">
                            <p class="text-xs text-slate-400 mt-1">Enter the DEVICE_ID configured on the IoT client device.</p>
                        </div>
                        
                        <div>
                            <label for="door_ip_address" class="block text-sm font-medium text-navy mb-2">
                                Solenoid IP Address
                                <span class="text-slate-400 font-normal">— IoT device network address</span>
                            </label>
                            <input type="text" id="door_ip_address" name="door_ip_address" 
                                value="{{ old('door_ip_address', $gate->door_ip_address) }}"
                                placeholder="e.g., 192.168.1.102"
                                class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue font-mono">
                            <p class="text-xs text-slate-400 mt-1">IP address of the ESP8266 solenoid controller.</p>
                        </div>
                        
                        <div class="bg-light-100/50 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full {{ $gate->integration_status === 'integrated' ? ($gate->isOnline() ? 'bg-success' : 'bg-warning') : 'bg-slate-300' }}"></div>
                                <span class="text-sm font-medium text-navy">
                                    Status: {{ $gate->integration_status_label }}
                                </span>
                            </div>
                            @if($gate->last_heartbeat_at)
                            <p class="text-xs text-slate-400 mt-2">Last heartbeat: {{ $gate->last_heartbeat_at->diffForHumans() }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @else
                {{-- DCFM view only --}}
                @if($gate->door_id)
                <div class="pt-6 border-t border-light-200">
                    <h3 class="text-lg font-semibold text-navy mb-4">Door Integration</h3>
                    <div class="bg-light-100/50 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Door ID:</span>
                            <span class="font-mono text-navy">{{ $gate->door_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">IP Address:</span>
                            <span class="font-mono text-navy">{{ $gate->door_ip_address ?? 'Not set' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">Status:</span>
                            <span class="inline-flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $gate->integration_status === 'integrated' ? ($gate->isOnline() ? 'bg-success' : 'bg-warning') : 'bg-slate-300' }}"></span>
                                {{ $gate->integration_status_label }}
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Contact SOC to modify door integration settings.</p>
                </div>
                @endif
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-light-200">
                    <a href="{{ route('gates.index') }}" class="px-4 py-2 text-slate hover:text-navy font-medium">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-sentinel-blue text-white font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
