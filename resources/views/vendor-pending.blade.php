<x-app-layout>
    <!-- Pending Approval Card -->
    <div class="flex items-center justify-center min-h-[calc(100vh-300px)]">
        <div class="max-w-2xl w-full mx-auto">
            <div class="bg-white rounded-3xl shadow-bento-hover border border-white/60 overflow-hidden">
                <!-- Header with Gradient -->
                <div class="bg-gradient-to-br from-warning/10 to-warning/5 p-8 text-center border-b border-warning/20">
                    <div class="w-20 h-20 bg-warning/20 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse-slow">
                        <svg class="w-10 h-10 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-display font-bold text-navy-900 mb-2">Account Pending Approval</h1>
                    <p class="text-slate-600 font-medium">Your vendor registration is being reviewed</p>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <div class="space-y-6">
                        <!-- User Info -->
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-sentinel-gradient flex items-center justify-center text-white font-display font-bold text-2xl shadow-lg">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-display font-bold text-lg text-navy-900">{{ auth()->user()->name }}</h3>
                                    <p class="text-slate-600">{{ auth()->user()->email }}</p>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-warning/10 text-warning border border-warning/20 mt-2">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        Pending Verification
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Status Message -->
                        <div class="space-y-4">
                            <h3 class="font-display font-bold text-navy-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                What's happening?
                            </h3>
                            <p class="text-slate-600 leading-relaxed">
                                Your vendor account has been successfully created and is currently being reviewed by our security team (DCFM or SOC). 
                                This verification process helps ensure the security and integrity of our data center access control system.
                            </p>
                        </div>

                        <!-- What to expect -->
                        <div class="space-y-4">
                            <h3 class="font-display font-bold text-navy-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                What to expect
                            </h3>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-sentinel-blue/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <span class="text-slate-600">Your face image is being verified for identity confirmation</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-sentinel-blue/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <span class="text-slate-600">Account credentials are being validated by security personnel</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-sentinel-blue/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-sentinel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <span class="text-slate-600">You'll receive full access once approved (typically within 24-48 hours)</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Action -->
                        <div class="pt-4 border-t border-slate-200">
                            <p class="text-sm text-slate-500 mb-4">
                                <strong>Need help?</strong> Contact your facility manager or security operations center for assistance.
                            </p>
                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2.5 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition-all duration-200">
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
