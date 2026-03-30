<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#0a2d9a] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#08247b] focus:bg-[#08247b] active:bg-[#061d66] focus:outline-none focus:ring-2 focus:ring-[#0a2d9a] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}
    x-data="{ loading: false }"
    x-on:click="loading = true"
    x-bind:disabled="loading"
>
    <span x-show="!loading">{{ $slot }}</span>
    <span x-show="loading" class="inline-flex items-center">
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
        Processing...
    </span>
</button>
