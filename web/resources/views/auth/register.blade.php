<x-guest-layout>
    <div class="mb-6 text-center">
        <h3 class="font-display font-bold text-2xl text-navy-900">Vendor Registration</h3>
        <p class="text-slate-500 font-sans text-sm">Create your vendor account to request access</p>
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

        <!-- Confirm Password -->
        <div class="group">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-navy-700 font-medium ml-1 mb-1" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full bg-slate-50 border-gray-200 focus:border-sentinel-blue focus:ring-sentinel-blue/20 rounded-xl py-2.5 px-4 transition-all duration-200 group-hover:bg-white"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password"
                            placeholder="Re-enter your password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Face Image Upload/Webcam -->
        <div class="mt-4">
            <x-input-label :value="__('Face Photo (Required)')" class="text-navy-700 font-medium ml-1 mb-2" />
            
            <!-- Hidden input for base64 image -->
            <input type="hidden" name="face_image" id="face_image" required>
            
            <!-- Preview Container -->
            <div id="previewContainer" class="hidden mb-4">
                <div class="relative inline-block">
                    <img id="facePreview" class="w-32 h-32 rounded-2xl mx-auto object-cover shadow-lg ring-4 ring-white">
                    <button type="button" onclick="removeFaceImage()" class="absolute -top-2 -right-2 w-8 h-8 bg-error text-white rounded-full hover:bg-error/80 transition-colors flex items-center justify-center shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Webcam Container -->
            <div id="webcamContainer" class="hidden mb-4">
                <video id="webcam" class="w-full rounded-xl shadow-lg" autoplay playsinline style="transform: scaleX(-1);"></video>
                <div class="flex gap-3 mt-4">
                    <button type="button" onclick="capturePhoto()" class="flex-1 px-4 py-2.5 bg-sentinel-gradient text-white font-bold rounded-xl hover:shadow-glow transition-all duration-200">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Capture Photo
                    </button>
                    <button type="button" onclick="stopWebcam()" class="px-4 py-2.5 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition-all duration-200">
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Upload Options -->
            <div id="uploadOptions">
                <div class="grid grid-cols-2 gap-4">
                    <!-- Upload File Option -->
                    <div class="border-2 border-dashed border-gray-300 hover:border-sentinel-blue rounded-xl p-6 text-center bg-slate-50 hover:bg-white transition-all duration-200 cursor-pointer group" id="uploadOption">
                        <input type="file" id="faceFileInput" accept="image/*" class="hidden">
                        <svg class="w-10 h-10 mx-auto text-slate-400 group-hover:text-sentinel-blue transition-colors mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm text-slate-600 font-medium">Upload Photo</p>
                        <p class="text-xs text-slate-400 mt-1">Click to browse</p>
                    </div>

                    <!-- Webcam Option -->
                    <button type="button" onclick="startWebcam()" class="border-2 border-dashed border-gray-300 hover:border-sentinel-blue rounded-xl p-6 text-center bg-slate-50 hover:bg-white transition-all duration-200 group">
                        <svg class="w-10 h-10 mx-auto text-slate-400 group-hover:text-sentinel-blue transition-colors mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm text-slate-600 font-medium">Use Webcam</p>
                        <p class="text-xs text-slate-400 mt-1">Take a selfie</p>
                    </button>
                </div>
            </div>
            
            <x-input-error :messages="$errors->get('face_image')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-slate-600 hover:text-navy-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Have an account?') }}
            </a>

            <x-primary-button class="ms-4 bg-sentinel-gradient hover:shadow-glow hover:scale-[1.01] transition-all duration-200 rounded-xl font-bold font-bricolage text-base py-2.5 px-6">
                {{ __('Register as Vendor') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        const faceImageInput = document.getElementById('face_image');
        const previewContainer = document.getElementById('previewContainer');
        const uploadOptions = document.getElementById('uploadOptions');
        const facePreview = document.getElementById('facePreview');
        const fileInput = document.getElementById('faceFileInput');
        const uploadOption = document.getElementById('uploadOption');
        const webcamContainer = document.getElementById('webcamContainer');
        const webcam = document.getElementById('webcam');
        let stream = null;

        // File upload
        uploadOption.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', (e) => handleFile(e.target.files[0]));

        function handleFile(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                faceImageInput.value = e.target.result;
                facePreview.src = e.target.result;
                previewContainer.classList.remove('hidden');
                uploadOptions.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        // Webcam functions
        async function startWebcam() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'user'
                    } 
                });
                webcam.srcObject = stream;
                uploadOptions.classList.add('hidden');
                webcamContainer.classList.remove('hidden');
            } catch (err) {
                alert('Unable to access webcam. Please upload an image instead.');
                console.error('Webcam error:', err);
            }
        }

        function capturePhoto() {
            const canvas = document.createElement('canvas');
            canvas.width = webcam.videoWidth;
            canvas.height = webcam.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(webcam, 0, 0);
            
            const imageData = canvas.toDataURL('image/jpeg', 0.9);
            faceImageInput.value = imageData;
            facePreview.src = imageData;
            
            stopWebcam();
            previewContainer.classList.remove('hidden');
        }

        function stopWebcam() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            webcamContainer.classList.add('hidden');
            uploadOptions.classList.remove('hidden');
        }

        function removeFaceImage() {
            faceImageInput.value = '';
            facePreview.src = '';
            fileInput.value = '';
            previewContainer.classList.add('hidden');
            uploadOptions.classList.remove('hidden');
        }
    </script>
</x-guest-layout>
