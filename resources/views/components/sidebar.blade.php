    <div x-data="{ open: false }" class="relative h-full">
        <!-- Burger button (modern SVG) -->
        <button 
            @click="open = !open; window.dispatchEvent(new CustomEvent('sidebar-toggled', { detail: open }));" 
            :style="open ? 'left: 160px;' : 'left: 8px;'"
            class="fixed top-16 z-50 bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 focus:outline-none transition-all duration-200">
            
            <!-- Modern Hamburger Icon -->
            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>

            <!-- Modern Close Icon -->
            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Sidebar -->
        <aside :class="open ? 'w-52' : 'w-16'" class="fixed left-0 top-0 h-full bg-red-600 flex flex-col py-6 px-2 transition-all duration-200 z-40 overflow-x-hidden">
            <!-- Logo/Title -->
             <a href="/dashboard">
                <div class="mb-10 flex flex-col items-center">
                    <span 
                        class="text-3xl font-extrabold text-yellow-300 leading-tight tracking-tight text-center" 
                        x-show="open"
                        style="text-shadow: 1px 1px 0 black, -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black;">
                        RV<br>
                        <span class="text-yellow-400 text-lg font-semibold">
                            GLASS <span class="text-yellow-300">and</span><br>ALUMINUM SUPPLY
                        </span>
                    </span>

                    <span 
                        class="text-3xl font-extrabold text-yellow-300 leading-tight tracking-tight text-center" 
                        x-show="!open"
                        style="text-shadow: 1px 1px 0 black, -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black;">
                        RV
                    </span>
                </div>
            </a>



            <!-- Navigation Links -->
            <nav class="flex-1 flex flex-col gap-2 mt-3">
                <!-- Example: Dashboard -->
                <a href="/dashboard" class="flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('dashboard') ? 'bg-red-700' : '' }}">
                    <span class="flex items-center gap-3">
                        <!-- Modern Home Icon -->
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span x-show="open">Dashboard</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                <!-- Sales -->
                <a href="/sales" class="flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('sales') ? 'bg-red-700' : '' }}">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 7v7m-7-7h14" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span x-show="open">Sales</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                <!-- Inventory -->
                <a href="/inventory" class="flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('inventory') ? 'bg-red-700' : '' }}">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 3v4M8 3v4M4 11h16" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span x-show="open">Inventory</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                <!-- Reports -->
                <a href="/reports" class="flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('reports') ? 'bg-red-700' : '' }}">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 4h6a2 2 0 002-2v-5a2 2 0 00-2-2h-6a2 2 0 00-2 2v5a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span x-show="open">Reports</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <!-- Branches (only admin can see)-->
                @if(auth()->user()->role === 'admin')
                <a href="/branches" class="flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('branches') ? 'bg-red-700' : '' }}">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span x-show="open">Branches</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                @endif
                <!-- Users (only admin can see) -->
                @if(auth()->user()->role === 'admin')
                    <a href="/users" class="flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('users') ? 'bg-red-700' : '' }}">
                        <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 4a4 4 0 10-8 0 4 4 0 008 0z" />
                        </svg>
                            <span x-show="open">Users</span>
                        </span>
                        <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                @endif
            </nav>
        </aside>
    </div>