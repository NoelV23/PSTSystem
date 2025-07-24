<form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
    @csrf
    @method('DELETE')
    <div class="text-red-700 font-bold mb-2">Delete Account</div>
    <div class="text-gray-700 mb-2">Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you want to permanently delete your account.</div>
    <div>
        <input type="password" name="password" required placeholder="Password" class="w-full px-4 py-2 border border-red-200 rounded focus:outline-none focus:ring-2 focus:ring-red-400">
    </div>
    <div class="flex flex-col sm:flex-row gap-2 justify-end">
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2 rounded transition">Delete Account</button>
    </div>
</form>
