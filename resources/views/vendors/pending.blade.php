<!-- resources/views/vendors/pending.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-display font-bold text-2xl text-navy-900 leading-tight">
                {{ __('Pending Approvals') }}
            </h2>
            <p class="text-slate-500 text-sm mt-1">Review and approve vendor identities for access.</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto"

        @if (session('status'))
            <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded-xl mb-6 flex items-center gap-3 animate-in fade-in slide-in-from-top-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('status') }}
            </div>
        @endif

        @if ($vendors->isEmpty())
            <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-12 text-center border border-white/60 shadow-bento">
                <div class="w-16 h-16 bg-sentinel-blue/5 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-sentinel-blue/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-navy-900">All Caught Up</h3>
                <p class="text-slate-500 mt-1">There are no pending vendor approvals at the moment.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($vendors as $vendor)
                    <div class="bg-white rounded-2xl shadow-bento hover:shadow-bento-hover transition-all duration-300 overflow-hidden border border-slate-100 group">
                        <div class="relative bg-navy-900 h-24 overflow-hidden">
                            <div class="absolute inset-0 bg-mesh opacity-50"></div>
                            <div class="absolute bottom-0 left-0 right-0 h-1/2 bg-gradient-to-t from-black/50 to-transparent"></div>
                        </div>
                        
                        <div class="px-6 pb-6 relative">
                            <!-- Face Image Avatar -->
                            <div class="-mt-12 mb-4 relative z-10">
                                <div class="w-24 h-24 rounded-2xl overflow-hidden border-4 border-white shadow-lg mx-auto bg-slate-100">
                                    @if ($vendor->face_image)
                                        <img src="{{ $vendor->face_image }}" alt="Face" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="text-center mb-6">
                                <h3 class="text-xl font-bold text-navy-900 font-display">{{ $vendor->name }}</h3>
                                <p class="text-sm text-slate-500">{{ $vendor->email }}</p>
                                <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning/10 text-warning">
                                    Pending Verification
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3">
                                <form method="POST" action="{{ route('vendors.approve', $vendor) }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full flex justify-center items-center gap-2 bg-sentinel-blue hover:bg-sentinel-blue-dark text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-sentinel-blue/30 transition-all duration-200 transform hover:scale-[1.02]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Approve Access
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
