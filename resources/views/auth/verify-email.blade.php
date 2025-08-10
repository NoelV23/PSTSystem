<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Verify Email</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('Please verify your email address by clicking the link we sent to your inbox. If you didn\'t receive it, you can request another below.') }}</p>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-green-700 bg-green-100 border border-green-200 rounded px-3 py-2">
                    {{ __('A new verification link has been sent to the email address on file.') }}
                </div>
            @endif

            <div class="mt-4 flex items-center justify-between">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-primary-button class="bg-red-600 hover:bg-red-700 focus:ring-red-500">
                        {{ __('Resend Email') }}
                    </x-primary-button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
