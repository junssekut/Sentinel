<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="text-slate hover:text-navy">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-navy leading-tight">
                {{ __('Register User') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-light-200 overflow-hidden">
            <form method="POST" action="{{ route('users.store') }}" class="p-6 space-y-6" id="userForm">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-navy mb-2">Full Name *</label>
                    <input type="text" id="name" name="name" required
                        value="{{ old('name') }}"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue"
                        placeholder="John Doe">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-navy mb-2">Email *</label>
                    <input type="email" id="email" name="email" required
                        value="{{ old('email') }}"
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue"
                        placeholder="john@example.com">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-navy mb-2">Password *</label>
                    <input type="password" id="password" name="password" required
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue"
                        placeholder="Minimum 8 characters">
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-navy mb-2">Role *</label>
                    <select id="role" name="role" required
                        class="w-full rounded-lg border-light-200 focus:ring-sentinel-blue focus:border-sentinel-blue">
                        <option value="vendor" {{ old('role') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                        <option value="dcfm" {{ old('role') === 'dcfm' ? 'selected' : '' }}>DCFM (Facility Manager)</option>
                        <option value="soc" {{ old('role') === 'soc' ? 'selected' : '' }}>SOC (Security Center)</option>
                    </select>
                </div>

                <!-- Face Image Upload -->
                <div>
                    <label class="block text-sm font-medium text-navy mb-2">Face Image *</label>
                    <div class="border-2 border-dashed border-light-200 rounded-lg p-6 text-center" id="dropZone">
                        <input type="hidden" name="face_image" id="face_image" required>
                        <input type="file" id="faceFileInput" accept="image/*" class="hidden">
                        <div id="previewContainer" class="hidden">
                            <img id="facePreview" class="w-32 h-32 rounded-full mx-auto object-cover mb-3">
                            <button type="button" onclick="removeFaceImage()" class="text-sm text-error hover:text-error/80">Remove</button>
                        </div>
                        <div id="uploadPrompt">
                            <svg class="w-12 h-12 mx-auto text-light-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-slate">Click to upload or drag and drop</p>
                            <p class="text-xs text-slate">PNG, JPG up to 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3 pt-4 border-t border-light-200">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 text-slate hover:text-navy font-medium">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-sentinel-blue text-white font-medium rounded-lg hover:bg-sentinel-blue-dark transition-colors">
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
            dropZone.classList.add('border-sentinel-blue');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-sentinel-blue'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-sentinel-blue');
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
