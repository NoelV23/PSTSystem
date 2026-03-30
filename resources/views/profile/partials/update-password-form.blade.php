<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-bold text-red-700 mb-1">Current Password</label>
            <input type="password" name="current_password" required class="w-full px-4 py-2 border border-red-200 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
            <label class="block text-sm font-bold text-yellow-700 mb-1">New Password</label>
            <input type="password" name="password" required class="w-full px-4 py-2 border border-yellow-200 rounded focus:outline-none focus:ring-2 focus:ring-yellow-400">
        </div>
        <div>
            <label class="block text-sm font-bold text-yellow-700 mb-1">Confirm New Password</label>
            <input type="password" name="password_confirmation" required class="w-full px-4 py-2 border border-yellow-200 rounded focus:outline-none focus:ring-2 focus:ring-yellow-400">
        </div>
        <div class="flex flex-col sm:flex-row gap-2 justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-red-600 text-white font-bold px-6 py-2 rounded transition">Update Password</button>
        </div>
    </form>
</section>
