<x-guest-layout>
    <div class="mb-6 text-center">
        <h3 class="font-display font-bold text-2xl text-navy-900">Create Account</h3>
        <p class="text-slate-500 font-sans text-sm">Join Sentinel to manage access</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div class="group">
            <x-input-label for="name" :value="__('Full Name')" class="text-navy-700 font-medium ml-1 mb-1" />
            <x-text-input id="name" class="block mt-1 w-full bg-slate-50 border-gray-200 focus:border-sentinel-blue focus:ring-sentinel-blue/20 rounded-xl py-2.5 px-4 transition-all duration-200 group-hover:bg-white" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="group">
            <x-input-label for="email" :value="__('Email')" class="text-navy-700 font-medium ml-1 mb-1" />
            <x-text-input id="email" class="block mt-1 w-full bg-slate-50 border-gray-200 focus:border-sentinel-blue focus:ring-sentinel-blue/20 rounded-xl py-2.5 px-4 transition-all duration-200 group-hover:bg-white" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="group">
            <x-input-label for="password" :value="__('Password')" class="text-navy-700 font-medium ml-1 mb-1" />
            <x-text-input id="password" class="block mt-1 w-full bg-slate-50 border-gray-200 focus:border-sentinel-blue focus:ring-sentinel-blue/20 rounded-xl py-2.5 px-4 transition-all duration-200 group-hover:bg-white"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            placeholder="Min. 8 characters" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Face Image Upload -->
        <div class="mt-4">
            <x-input-label :value="__('Face Image')" class="text-navy-700 font-medium ml-1 mb-1" />
            <div class="mt-1 border-2 border-dashed border-gray-300 hover:border-sentinel-blue rounded-xl p-6 text-center bg-slate-50 hover:bg-white transition-all duration-200 group cursor-pointer" id="dropZone">
                <input type="hidden" name="face_image" id="face_image" required>
                <input type="file" id="faceFileInput" accept="image/*" class="hidden">
                <div id="previewContainer" class="hidden">
                    <img id="facePreview" class="w-24 h-24 rounded-full mx-auto object-cover mb-3 shadow-md ring-2 ring-white">
                    <button type="button" onclick="removeFaceImage()" class="text-sm text-error hover:text-error/80 font-medium">Change Image</button>
                </div>
                <div id="uploadPrompt" class="group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-10 h-10 mx-auto text-slate-400 group-hover:text-sentinel-blue transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-slate-600 font-medium">Drop face photo here</p>
                    <p class="text-xs text-slate-400">or click to browse</p>
                </div>
            </div>
            <x-input-error :messages="$errors->get('face_image')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-slate-600 hover:text-navy-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Have an account?') }}
            </a>

            <x-primary-button class="ms-4 bg-sentinel-gradient hover:shadow-glow hover:scale-[1.01] transition-all duration-200 rounded-xl font-bold font-bricolage text-base py-2.5 px-6">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

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
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-sentinel-blue', 'bg-blue-50');
        });
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
            // Effectively just triggers the file input again to "change" it
            fileInput.click();
        }
    </script>
</x-guest-layout>
