<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-3">Confirm Password</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('Please confirm your password to continue.') }}</p>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-blue-500"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-700" />
                </div>

                <div class="flex justify-end mt-6">
                    <x-primary-button class="bg-red-600 hover:bg-red-700 focus:ring-blue-500">
                        {{ __('Confirm') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
