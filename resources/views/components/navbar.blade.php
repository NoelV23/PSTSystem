<nav x-data="{ userOpen: false }" class="w-full bg-white shadow px-4 py-3 flex justify-end items-center">
    <div class="relative">
        <button @click="userOpen = !userOpen" class="flex items-center gap-2 px-3 py-1 rounded hover:bg-gray-100 focus:outline-none">
            <span class="text-gray-600">{{ Auth::user()->name ?? 'User' }}</span>
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <!-- Dropdown Menu -->
        <div 
            x-show="userOpen" 
            @click.away="userOpen = false" 
            x-transition 
            class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg z-50"
        >
            <a href="/profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
            <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                @csrf
                <button type="button" onclick="handleLogout()" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</button>
            </form>
        </div>
    </div>
</nav>
