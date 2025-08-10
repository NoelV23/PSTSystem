<x-guest-layout>
    @php
        $imageUrl = asset('images/rv-glass-outside.jpg'); 
        $logoUrl = asset('images/rv-glass-logo.png'); 
    @endphp

    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg flex overflow-hidden w-full max-w-3xl">
            <!-- Left: Image -->
            <div class="w-1/2 h-full">
                <img src="{{ $imageUrl }}" alt="RV Glass and Aluminum Supply" class="object-cover h-full w-full">
            </div>
            <!-- Right: Login Form -->
            <div class="w-1/2 bg-[#E31C23] flex flex-col items-center justify-center p-8">
                <img src="{{ $logoUrl }}" alt="RV Glass and Aluminum Supply Logo" class="mb-6 w-64">
                <h2 class="text-white text-3xl font-bold mb-6">Login</h2>
                <form method="POST" action="{{ route('login') }}" class="w-full max-w-xs">
                    @csrf
                    <!-- Email Address -->
                    <div class="mb-4">
                        <input id="email" name="email" type="email" placeholder="Email Address" required autofocus autocomplete="username" class="w-full px-4 py-2 rounded bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-yellow-200" />
                    </div>
                    <!-- Password -->
                    <div class="mb-4 relative">
                        <input id="password" name="password" type="password" placeholder="Password" required autocomplete="current-password" class="w-full px-4 py-2 rounded bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-yellow-200" />
                        <!-- Eye icon placeholder -->
                        <span class="absolute right-3 top-3 text-gray-400 cursor-pointer">
                            <!-- SVG icon here -->
                        </span>
                    </div>
                    <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-2 rounded mb-2 transition">Log In</button>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="block text-center text-yellow-200 hover:text-yellow-100 text-sm">Forgot password?</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
