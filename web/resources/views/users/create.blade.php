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

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-bento border border-white/60 overflow-hidden">
            <form method="POST" action="{{ route('users.store') }}" class="p-8 space-y-6" id="userForm">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column: Basic Info -->
                    <div class="space-y-6">
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

                        <!-- Password Info -->
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                            <div class="flex items-center gap-2 text-sentinel-blue">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                <span class="text-sm font-bold">Password Auto-Generated</span>
                            </div>
                            <p class="text-xs text-blue-600 mt-1">A secure password will be automatically generated and shown after creation.</p>
                        </div>
                    </div>

                    <!-- Right Column: Face Capture -->
                    <div class="space-y-4">
                        <label class="block text-sm font-bold text-navy-900 mb-2 ml-1">Face Enrollment (Optional)</label>
                        
                        <!-- Camera Selection -->
                        <div class="flex gap-2 mb-3">
                            <select id="cameraSelect" class="flex-1 rounded-lg border-gray-200 text-sm py-2 px-3">
                                <option value="">Loading cameras...</option>
                            </select>
                            <button type="button" id="toggleCameraBtn" onclick="toggleCamera()" 
                                class="px-4 py-2 bg-sentinel-blue text-white text-sm font-bold rounded-lg hover:bg-sentinel-blue-dark transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                <span id="cameraBtnText">Start Camera</span>
                            </button>
                        </div>

                        <!-- Camera View / Drop Zone -->
                        <div class="relative">
                            <!-- Webcam View -->
                            <div id="webcamContainer" class="hidden">
                                <video id="webcamVideo" autoplay playsinline class="w-full aspect-square object-cover rounded-2xl border-2 border-sentinel-blue"></video>
                                <button type="button" onclick="capturePhoto()" 
                                    class="absolute bottom-4 left-1/2 -translate-x-1/2 px-6 py-2.5 bg-success text-white text-sm font-bold rounded-full shadow-lg hover:bg-success/90 transition-all flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Capture Face
                                </button>
                            </div>

                            <!-- Drop Zone (file upload fallback) -->
                            <div id="dropZone" class="border-2 border-dashed border-gray-300 hover:border-sentinel-blue rounded-2xl p-8 text-center bg-slate-50 hover:bg-white transition-all duration-200 cursor-pointer group aspect-square flex flex-col items-center justify-center">
                                <input type="hidden" name="face_image" id="face_image">
                                <input type="file" id="faceFileInput" accept="image/*" class="hidden">
                                
                                <div id="previewContainer" class="hidden w-full h-full flex flex-col items-center justify-center">
                                    <img id="facePreview" class="w-40 h-40 rounded-2xl object-cover mb-4 shadow-lg ring-4 ring-white">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-success/10 text-success mb-3">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        Face Captured
                                    </span>
                                    <button type="button" onclick="removeFaceImage()" class="text-sm text-error hover:text-error/80 font-bold hover:underline">Retake Photo</button>
                                </div>
                                
                                <div id="uploadPrompt" class="group-hover:scale-105 transition-transform duration-200">
                                    <div class="w-20 h-20 bg-sentinel-blue/10 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-sentinel-blue/20 transition-colors">
                                        <svg class="w-10 h-10 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <p class="text-sm text-slate-600 font-bold mb-1">Use camera above or drop image here</p>
                                    <p class="text-xs text-slate-400">PNG, JPG up to 5MB</p>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 text-center">
                            Face enrollment is optional. If provided, it will be auto-approved as a trusted admin source.
                        </p>
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

    <!-- Hidden canvas for capturing -->
    <canvas id="captureCanvas" class="hidden"></canvas>

    <script>
        let stream = null;
        let cameraActive = false;
        const video = document.getElementById('webcamVideo');
        const canvas = document.getElementById('captureCanvas');
        const cameraSelect = document.getElementById('cameraSelect');
        const webcamContainer = document.getElementById('webcamContainer');
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('faceFileInput');
        const faceImageInput = document.getElementById('face_image');
        const previewContainer = document.getElementById('previewContainer');
        const uploadPrompt = document.getElementById('uploadPrompt');
        const facePreview = document.getElementById('facePreview');

        // Initialize camera list
        async function initCameras() {
            try {
                // Request permission first
                await navigator.mediaDevices.getUserMedia({ video: true }).then(s => s.getTracks().forEach(t => t.stop()));
                
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(d => d.kind === 'videoinput');
                
                cameraSelect.innerHTML = '';
                if (videoDevices.length === 0) {
                    cameraSelect.innerHTML = '<option value="">No cameras found</option>';
                    return;
                }
                
                videoDevices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.textContent = device.label || `Camera ${index + 1}`;
                    cameraSelect.appendChild(option);
                });
            } catch (err) {
                console.error('Camera init error:', err);
                cameraSelect.innerHTML = '<option value="">Camera access denied</option>';
            }
        }

        async function toggleCamera() {
            if (cameraActive) {
                stopCamera();
            } else {
                await startCamera();
            }
        }

        async function startCamera() {
            const deviceId = cameraSelect.value;
            if (!deviceId) return;

            try {
                const constraints = {
                    video: { 
                        deviceId: { exact: deviceId },
                        width: { ideal: 640 },
                        height: { ideal: 640 }
                    }
                };
                
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = stream;
                
                webcamContainer.classList.remove('hidden');
                dropZone.classList.add('hidden');
                document.getElementById('cameraBtnText').textContent = 'Stop Camera';
                document.getElementById('toggleCameraBtn').classList.remove('bg-sentinel-blue');
                document.getElementById('toggleCameraBtn').classList.add('bg-error');
                cameraActive = true;
            } catch (err) {
                console.error('Camera start error:', err);
                alert('Could not access camera: ' + err.message);
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.srcObject = null;
            webcamContainer.classList.add('hidden');
            dropZone.classList.remove('hidden');
            document.getElementById('cameraBtnText').textContent = 'Start Camera';
            document.getElementById('toggleCameraBtn').classList.add('bg-sentinel-blue');
            document.getElementById('toggleCameraBtn').classList.remove('bg-error');
            cameraActive = false;
        }

        function capturePhoto() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            setFaceImage(dataUrl);
            stopCamera();
        }

        function setFaceImage(dataUrl) {
            faceImageInput.value = dataUrl;
            facePreview.src = dataUrl;
            previewContainer.classList.remove('hidden');
            uploadPrompt.classList.add('hidden');
        }

        function removeFaceImage() {
            faceImageInput.value = '';
            facePreview.src = '';
            previewContainer.classList.add('hidden');
            uploadPrompt.classList.remove('hidden');
            fileInput.value = '';
        }

        // File upload handlers
        dropZone.addEventListener('click', (e) => {
            if (!previewContainer.classList.contains('hidden')) return;
            fileInput.click();
        });
        
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
            reader.onload = (e) => setFaceImage(e.target.result);
            reader.readAsDataURL(file);
        }

        // Camera selection change
        cameraSelect.addEventListener('change', async () => {
            if (cameraActive) {
                stopCamera();
                await startCamera();
            }
        });

        // Initialize
        initCameras();
    </script>
</x-app-layout>
