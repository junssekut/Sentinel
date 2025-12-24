<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="text-slate-500 hover:text-navy-900 hover:scale-110 transition-all duration-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-display font-bold text-2xl text-navy-900 leading-tight">
                    {{ __('Register User') }}
                </h2>
                <p class="text-slate-500 text-sm mt-1">Create a new user account for the system.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
            <form method="POST" action="{{ route('users.store') }}" class="p-8 space-y-6" id="userForm">
                @csrf

                <!-- Name -->
                <div class="group">
                    <label for="name" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Full Name *</label>
                    <input type="text" id="name" name="name" required
                        value="{{ old('name') }}"
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium"
                        placeholder="John Doe">
                </div>

                <!-- Email -->
                <div class="group">
                    <label for="email" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Email *</label>
                    <input type="email" id="email" name="email" required
                        value="{{ old('email') }}"
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium"
                        placeholder="john@company.com">
                </div>

                <!-- Password -->
                <div class="group">
                    <label for="password" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Password *</label>
                    <input type="password" id="password" name="password" required
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium"
                        placeholder="Minimum 8 characters">
                </div>

                <!-- Role -->
                <div class="group">
                    <label for="role" class="block text-sm font-bold text-navy-900 mb-2 ml-1">Role *</label>
                    <select id="role" name="role" required
                        class="w-full rounded-xl border-gray-200 focus:ring-sentinel-blue focus:border-sentinel-blue bg-slate-50 hover:bg-white transition-all duration-200 py-3 px-4 font-medium">
                        <option value="vendor" {{ old('role') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                        <option value="dcfm" {{ old('role') === 'dcfm' ? 'selected' : '' }}>DCFM (Facility Manager)</option>
                        <option value="soc" {{ old('role') === 'soc' ? 'selected' : '' }}>SOC (Security Center)</option>
                    </select>
                </div>

                <!-- Face Image Upload -->
                <div>
                    <label class="block text-sm font-bold text-navy-900 mb-2 ml-1">Face Image *</label>
                    <div class="border-2 border-dashed border-gray-300 hover:border-sentinel-blue rounded-2xl p-8 text-center bg-slate-50 hover:bg-white transition-all duration-200 cursor-pointer group" id="dropZone">
                        <input type="hidden" name="face_image" id="face_image" required>
                        <input type="file" id="faceFileInput" accept="image/*" class="hidden">
                        <div id="previewContainer" class="hidden">
                            <img id="facePreview" class="w-32 h-32 rounded-2xl mx-auto object-cover mb-4 shadow-lg ring-4 ring-white">
                            <button type="button" onclick="removeFaceImage()" class="text-sm text-error hover:text-error/80 font-bold hover:underline decoration-2 underline-offset-2">Change Image</button>
                        </div>
                        <div id="uploadPrompt" class="group-hover:scale-105 transition-transform duration-200">
                            <div class="w-16 h-16 bg-sentinel-blue/10 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-sentinel-blue/20 transition-colors">
                                <svg class="w-8 h-8 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <p class="text-sm text-slate-600 font-bold mb-1">Drop face photo here</p>
                            <p class="text-xs text-slate-400">or click to browse • PNG, JPG up to 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('users.index') }}" class="px-6 py-2.5 text-slate-600 hover:text-navy-900 font-bold hover:bg-slate-100 rounded-xl transition-all duration-200">Cancel</a>
                    <button type="submit" class="px-8 py-2.5 bg-sentinel-gradient text-white font-bold rounded-xl hover:shadow-glow hover:scale-[1.02] transition-all duration-200 shadow-lg shadow-sentinel-blue/30">
                        Register User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('faceFileInput');
        const faceImageInput = document.getElementById('face_image');
        const previewContainer = document.getElementById('previewContainer');
        const uploadPrompt = document.getElementById('uploadPrompt');
        const facePreview = document.getElementById('facePreview');

        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-sentinel-blue', 'bg-blue-50');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-sentinel-blue', 'bg-blue-50'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-sentinel-blue', 'bg-blue-50');
            handleFile(e.dataTransfer.files[0]);
        });
        fileInput.addEventListener('change', (e) => handleFile(e.target.files[0]));

        function handleFile(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                faceImageInput.value = e.target.result;
                facePreview.src = e.target.result;
                previewContainer.classList.remove('hidden');
                uploadPrompt.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        function removeFaceImage() {
            faceImageInput.value = '';
            facePreview.src = '';
            previewContainer.classList.add('hidden');
            uploadPrompt.classList.remove('hidden');
            fileInput.value = '';
        }
    </script>
</x-app-layout>
