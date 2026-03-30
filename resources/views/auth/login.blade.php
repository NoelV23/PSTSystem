<x-guest-layout>
    @php
        $logoUrl = asset('images/PSTLogo.jpg');
    @endphp

    <div class="min-h-screen flex items-center justify-center px-4 py-8 bg-slate-100">
        <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
            <div class="hidden md:flex flex-col justify-between bg-gradient-to-br from-blue-700 via-blue-800 to-blue-900 p-10 text-white">
                <div>
                    <p class="uppercase tracking-[0.2em] text-yellow-300 text-xs font-semibold">Welcome to</p>
                    <h1 class="text-3xl font-bold mt-2 leading-tight">PSTSystem</h1>
                    <p class="mt-4 text-blue-100 text-sm">Polytech Steel Trading inventory and operations platform.</p>
                </div>
                <div class="rounded-xl bg-white/10 border border-white/20 p-4">
                    <p class="text-sm text-blue-50">Fast access to sales, inventory, purchases, and reporting in one secure dashboard.</p>
                </div>
            </div>

            <div class="p-8 md:p-12 flex flex-col justify-center">
                <div class="flex justify-center mb-6">
                    <img src="{{ $logoUrl }}" alt="PST Logo" class="w-40 h-40 object-contain rounded-xl shadow-sm ring-1 ring-slate-200">
                </div>

                <h2 class="text-center text-2xl font-bold text-slate-800">Sign in to PSTSystem</h2>
                <p class="text-center text-sm text-slate-500 mt-1 mb-8">Use your account credentials to continue.</p>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <input id="email" name="email" type="email" placeholder="Email Address" required autofocus autocomplete="username"
                            class="w-full px-4 py-3 rounded-lg bg-white border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600" />
                    </div>

                    <div>
                        <input id="password" name="password" type="password" placeholder="Password" required autocomplete="current-password"
                            class="w-full px-4 py-3 rounded-lg bg-white border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600" />
                    </div>

                    <button type="submit"
                        class="w-full bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-semibold py-3 rounded-lg transition shadow-sm">
                        Log In
                    </button>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="block text-center text-sm text-blue-700 hover:text-blue-900 font-medium">Forgot password?</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
