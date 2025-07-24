<div x-data="{ open: @entangle($attributes->wire('model')).defer ?? true }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" style="display: none;">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative">
        <button @click="open = false" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        {{ $slot }}
    </div>
</div>
