<div x-data="{ show: true }" x-show="show" class="fixed top-6 right-6 z-50">
    <div class="bg-green-500 text-white px-6 py-3 rounded shadow flex items-center gap-2">
        <span>{{ $message ?? 'Success!' }}</span>
        <button @click="show = false" class="ml-2 text-white hover:text-gray-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
</div> 