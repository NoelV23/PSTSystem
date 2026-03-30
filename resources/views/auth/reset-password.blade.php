<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Reset Password</h2>
            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-blue-500" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-700" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-blue-500" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-700" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                    <x-text-input id="password_confirmation" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-blue-500"
                                        type="password"
                                        name="password_confirmation" required autocomplete="new-password" />

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-700" />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <x-primary-button class="bg-red-600 hover:bg-red-700 focus:ring-blue-500">
                        {{ __('Reset Password') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
