<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#0a2d9a] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#08247b] active:bg-[#061d66] focus:outline-none focus:ring-2 focus:ring-[#0a2d9a] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
