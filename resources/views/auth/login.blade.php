<x-guest-layout>
    @php
        $logoUrl = asset('images/PSTLogoNoBG2.png');
    @endphp

    <style>
        /* Login page: static backdrop aligned with form (#f8fafc) + subtle PST blue / gold wash */
        .login-page-bg {
            min-height: 100vh;
            background-color: #e8edf4;
            background-image:
                radial-gradient(ellipse 95% 65% at 92% 6%, rgba(10, 45, 154, 0.09), transparent 52%),
                radial-gradient(ellipse 85% 55% at 4% 96%, rgba(244, 194, 13, 0.06), transparent 50%),
                linear-gradient(168deg, #f4f6f9 0%, #eef2f7 42%, #e4e9f2 100%);
        }

        /* Login logo column: light grid + soft vignette (static) */
        .login-logo-panel-grid {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.055) 1px, transparent 1px);
            background-size: 26px 26px;
            mask-image: radial-gradient(ellipse 78% 72% at 50% 48%, black 0%, transparent 72%);
            -webkit-mask-image: radial-gradient(ellipse 78% 72% at 50% 48%, black 0%, transparent 72%);
        }

        .login-logo-panel-vignette {
            background: radial-gradient(ellipse 85% 70% at 50% 45%, transparent 35%, rgba(8, 36, 123, 0.45) 100%);
        }

        /* Login form column: airy grid + soft brand wash (static, light theme) */
        .login-form-panel-wash {
            background:
                radial-gradient(ellipse 75% 55% at 100% 8%, rgba(10, 45, 154, 0.09), transparent 58%),
                radial-gradient(ellipse 65% 48% at 0% 92%, rgba(244, 194, 13, 0.07), transparent 52%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.5) 0%, transparent 38%, transparent 62%, rgba(241, 245, 249, 0.85) 100%);
        }

        .login-form-panel-grid {
            background-image:
                linear-gradient(rgba(15, 23, 42, 0.042) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15, 23, 42, 0.042) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: radial-gradient(ellipse 95% 88% at 50% 42%, black 0%, transparent 78%);
            -webkit-mask-image: radial-gradient(ellipse 95% 88% at 50% 42%, black 0%, transparent 78%);
        }
    </style>

    <div class="login-page-bg flex items-center justify-center p-3 sm:p-6 md:p-8" x-data="{ showPassword: false }">
        <div class="relative z-10 w-full max-w-6xl bg-white/95 rounded-2xl md:rounded-3xl shadow-2xl overflow-hidden border border-white/40 grid grid-cols-1 md:grid-cols-12 md:min-h-[80vh]">
            
            <div class="hidden md:block md:col-span-6 relative left-panel-sweep bg-gradient-to-br from-[#0a2d9a] via-[#0c34a8] to-[#071e66] p-6 lg:p-8">
                <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
                    <div class="login-logo-panel-vignette absolute inset-0 z-[1]"></div>
                    <div class="login-logo-panel-grid absolute inset-0 z-[2] opacity-90"></div>
                    <div class="absolute -top-28 -left-28 w-[22rem] h-[22rem] rounded-full bg-[#f4c20d]/18 blur-3xl z-0"></div>
                    <div class="absolute -bottom-20 -right-16 w-[26rem] h-[26rem] rounded-full bg-[#38bdf8]/12 blur-3xl z-0"></div>
                    <div class="absolute top-[12%] right-[8%] w-16 h-16 rounded-2xl border border-white/15 rotate-12 z-[3]"></div>
                    <div class="absolute bottom-[18%] left-[10%] w-10 h-10 rounded-lg border border-[#f4c20d]/25 -rotate-6 z-[3]"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[min(92%,26rem)] aspect-square rounded-full border border-white/[0.07] z-[3]"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[min(72%,20rem)] aspect-square rounded-full border border-white/[0.05] z-[3]"></div>
                    <div class="absolute top-0 right-0 w-28 h-28 border-t-2 border-r-2 border-[#f4c20d]/35 rounded-tr-[1.35rem] z-[3]"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 border-b-2 border-l-2 border-white/20 rounded-bl-[1.15rem] z-[3]"></div>
                </div>

                <div class="h-full min-h-[min(80vh,36rem)] flex flex-col items-center justify-center relative z-10 p-4 gap-5">
                    <img src="{{ $logoUrl }}" alt="PST Logo" class="w-full h-auto max-w-[620px] object-contain drop-shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
                    <p class="text-center text-white/90 text-sm sm:text-base font-semibold tracking-wide max-w-md px-2 drop-shadow-sm">Roofing services &amp; more</p>
                </div>
            </div>

            <div class="md:col-span-6 relative z-20 overflow-hidden bg-[#f8fafc]">
                <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                    <div class="login-form-panel-wash absolute inset-0 z-[1]"></div>
                    <div class="login-form-panel-grid absolute inset-0 z-[2] opacity-80"></div>
                    <div class="absolute -top-16 right-[-3rem] w-56 h-56 rounded-full bg-[#0a2d9a]/[0.07] blur-3xl z-0"></div>
                    <div class="absolute -bottom-12 left-[-2.5rem] w-52 h-52 rounded-full bg-[#f4c20d]/[0.12] blur-3xl z-0"></div>
                    <div class="absolute top-[22%] right-[6%] w-11 h-11 rounded-xl border border-slate-300/60 rotate-[-14deg] z-[3]"></div>
                    <div class="absolute bottom-[28%] right-[12%] w-7 h-7 rounded-md border border-[#0a2d9a]/20 rotate-12 z-[3]"></div>
                    <div class="absolute top-1/3 left-[4%] w-[min(55%,14rem)] h-px bg-gradient-to-r from-transparent via-[#0a2d9a]/15 to-transparent z-[3]"></div>
                    <div class="absolute top-0 left-0 w-24 h-24 border-t-2 border-l-2 border-[#0a2d9a]/20 rounded-tl-[1.25rem] z-[3]"></div>
                    <div class="absolute bottom-0 right-0 w-28 h-28 border-b-2 border-r-2 border-[#f4c20d]/30 rounded-br-[1.35rem] z-[3]"></div>
                </div>

                <div class="relative z-10 p-5 sm:p-8 lg:p-12 flex flex-col justify-center min-h-0 md:min-h-[80vh]">
                <div class="md:hidden flex justify-center mb-1">
                    <img src="{{ $logoUrl }}" alt="PST Logo" class="w-52 sm:w-60 h-auto object-contain">
                </div>

                <div class="text-[#0f172a] mb-0 md:mb-4">
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tighter">Sign In</h1>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <div class="relative group">
                            <input id="email" name="email" type="email" placeholder="name@company.com" required autofocus autocomplete="username"
                                class="w-full px-4 sm:px-6 py-3.5 sm:py-4 rounded-2xl bg-white border border-slate-300 text-[#0f172a] placeholder:text-slate-400 text-base sm:text-lg 
                                       focus:outline-none focus:ring-2 focus:ring-[#0a2d9a] focus:border-transparent transition duration-200 group-hover:border-white/30">
                            <label for="email" class="absolute -top-2.5 left-5 px-1 bg-[#f8fafc] text-xs font-semibold text-slate-500 uppercase tracking-wider">Email Address</label>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-500 font-medium" />
                    </div>

                    <div>
                        <div class="relative group">
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'" placeholder="Enter your password" required autocomplete="current-password"
                                class="w-full px-4 sm:px-6 py-3.5 sm:py-4 rounded-2xl bg-white border border-slate-300 text-[#0f172a] placeholder:text-slate-400 text-base sm:text-lg 
                                       focus:outline-none focus:ring-2 focus:ring-[#0a2d9a] focus:border-transparent transition duration-200 group-hover:border-white/30">
                            <label for="password" class="absolute -top-2.5 left-5 px-1 bg-[#f8fafc] text-xs font-semibold text-slate-500 uppercase tracking-wider">Password</label>
                            
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center text-slate-400 hover:text-[#0a2d9a] transition" aria-label="Toggle password visibility">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full hover:bg-slate-100/80 transition">
                                    <svg x-cloak x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                                        <circle cx="12" cy="12" r="2.75" />
                                    </svg>
                                    <svg x-cloak x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2 2 0 0 0 13.413 13.4" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 4.875c4.948 0 9.136 3.05 10.5 7.125a11.32 11.32 0 0 1-4.04 5.522M6.61 6.61A11.295 11.295 0 0 0 1.5 12c1.364 4.075 5.552 7.125 10.5 7.125a11.2 11.2 0 0 0 5.39-1.39" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500 font-medium" />
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-[#f4c20d] hover:text-[#ffef00] hover:underline underline-offset-2 transition">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full bg-[#0a2d9a] hover:bg-[#08247b] text-white font-extrabold py-4 sm:py-5 rounded-2xl text-base sm:text-lg tracking-wider transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/25 active:translate-y-0">
                        Log In
                    </button>
                </form>

                <div class="text-center mt-8 md:mt-12 text-slate-500 text-xs sm:text-sm font-medium">
                    &copy; {{ date('Y') }} Polytech Steel Trading
                </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>