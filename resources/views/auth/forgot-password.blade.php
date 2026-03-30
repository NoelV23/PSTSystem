<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-3">Forgot Password</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('Enter your email and we will send you a password reset link.') }}</p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-blue-500" type="email" name="email" :value="old('email')" required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-700" />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <!-- Back to login page -->
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-32">
                        {{ __('Back to login') }}
                    </a>
                    <!-- Send Reset Link -->
                    <x-primary-button class="bg-red-600 hover:bg-red-700 focus:ring-blue-500">
                        {{ __('Send Reset Link') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
