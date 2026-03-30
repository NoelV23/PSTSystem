<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')
        <div>
            <label class="block text-sm font-bold text-red-700 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full px-4 py-2 border border-red-200 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
            <label class="block text-sm font-bold text-red-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full px-4 py-2 border border-yellow-200 rounded focus:outline-none focus:ring-2 focus:ring-yellow-400">
        </div>
        <div class="flex flex-col sm:flex-row gap-2 justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-red-600 text-white font-bold px-6 py-2 rounded transition">Save</button>
        </div>
    </form>
</section>
