    <div x-data="{ open: false }" class="relative h-full">
        <!-- Burger button (modern SVG) -->
        <button 
            @click="open = !open; window.dispatchEvent(new CustomEvent('sidebar-toggled', { detail: open }));"
            :class="open ? 'left-40' : 'left-2'"
            class="fixed top-16 z-50 bg-blue-700 text-white p-2 rounded-lg hover:bg-blue-800 focus:outline-none transition-all duration-300 ease-in-out ml-2">
            
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
        <aside :class="open ? 'w-52' : 'w-16'" class="fixed left-0 top-0 h-full bg-blue-700 flex flex-col py-6 px-2 transition-all duration-300 ease-in-out z-40 overflow-x-hidden">
            <!-- Logo/Title -->
            <a href="/dashboard">
                <div class="mb-10 flex flex-col items-center">
                    <!-- When sidebar is open -->
                    <span 
                        x-show="open"
                        class="flex flex-row items-center justify-center h-16"
                        style="margin-top: -18px;"
                    >
                        <!-- Logo Image -->
                        <img src="{{ asset('images/PSTLogo.jpg') }}" alt="PST Logo" class="h-8 w-8 rounded object-cover">

                        <!-- Text beside logo -->
                        <span class="flex flex-col justify-center h-full">
                            <span class="text-white text-[11px] font-semibold leading-none" style="line-height: 1.1;">
                                Polytech
                            </span>
                            <span class="text-white text-[11px] font-semibold uppercase leading-none" style="line-height: 1.1;">
                                Steel Trading
                            </span>
                        </span>
                    </span>

                    <!-- When sidebar is closed -->
                    <span 
                        x-show="!open"
                        class="text-center mb-4"
                        style="margin-top: -6px;"
                    >
                        <img src="{{ asset('images/PSTLogo.jpg') }}" alt="PST Logo" class="h-8 w-8 mx-auto mt-1 rounded object-cover">
                    </span>
                </div>
            </a>


            <!-- Navigation Links -->
            <nav class="flex-1 flex flex-col gap-2 mt-3">
                <!-- Dashboard, hidden if user is staff -->
                @if(auth()->user()->role !== 'staff')
                <a href="/dashboard" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('dashboard') ? 'bg-blue-800' : '' }}" 
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"></path>
                        </svg>
                        <span x-show="open">Dashboard</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Tooltip -->
                    <div x-show="showTooltip && !open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                        Dashboard
                    </div>
                </a>
                @endif

                <!-- Sales -->
                <a href="/sales" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('sales') ? 'bg-blue-800' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 19h16M6 15l4-4 3 3 5-6" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span x-show="open">Sales</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Tooltip -->
                    <div x-show="showTooltip && !open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                        Sales
                    </div>
                </a>
                @if(auth()->user()->role !== 'staff')
                <!-- Purchases -->
                <a href="/purchases" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('purchases') ? 'bg-blue-800' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 5h2l2.2 10.2a2 2 0 002 1.6h7.8a2 2 0 001.9-1.4L21 8H7" stroke-linecap="round" stroke-linejoin="round"></path>
                            <circle cx="10" cy="19" r="1.5" fill="currentColor"></circle>
                            <circle cx="17" cy="19" r="1.5" fill="currentColor"></circle>
                        </svg>
                        <span x-show="open">Purchases</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Tooltip -->
                    <div x-show="showTooltip && !open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                        Purchases
                    </div>
                </a>

                <!-- Inventory -->
                <a href="/inventory" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('inventory') ? 'bg-blue-800' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 7l8-4 8 4-8 4-8-4z" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M4 12l8 4 8-4" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M4 17l8 4 8-4" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span x-show="open">Inventory</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Tooltip -->
                    <div x-show="showTooltip && !open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                        Inventory
                    </div>
                </a>
                @endif

                <!-- Reports (not accessible by staff) -->
                @if(auth()->user()->role !== 'staff')
                <a href="/reports" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('reports*') ? 'bg-blue-800' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 19h16M7 16V9m5 7V5m5 11v-4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span x-show="open">Reports</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Tooltip -->
                    <div x-show="showTooltip && !open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                        Reports
                    </div>
                </a>
                @endif

                <!-- Stock Adjustments (not accessible by staff) -->
                @if(auth()->user()->role !== 'staff')
                <a href="/stock-adjustments" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('stock-adjustments*') ? 'bg-blue-800' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 7h16M7 12h10M10 17h4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span x-show="open">Stock Adjustments</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Tooltip -->
                    <div x-show="showTooltip && !open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                        Stock Adjustments
                    </div>
                </a>
                @endif

                <!-- Products -->
                <a href="/products" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('products') ? 'bg-blue-800' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 7l8-4 8 4-8 4-8-4z" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M6 10v6a2 2 0 002 2h8a2 2 0 002-2v-6" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span x-show="open">Products</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Tooltip -->
                    <div x-show="showTooltip && !open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                        Products
                    </div>
                </a>

                <!-- Branches (only admin can see)-->
                @if(auth()->user()->role === 'admin')
                <a href="/branches" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('branches') ? 'bg-blue-800' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 21s7-4.5 7-10a7 7 0 10-14 0c0 5.5 7 10 7 10z" stroke-linecap="round" stroke-linejoin="round"></path>
                            <circle cx="12" cy="11" r="2.5" fill="currentColor"></circle>
                        </svg>
                        <span x-show="open">Branches</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Tooltip -->
                    <div x-show="showTooltip && !open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                        Branches
                    </div>
                </a>
                @endif

                <!-- Expenses (admin and manager only) -->
                <a href="/expenses" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('expenses') ? 'bg-blue-800' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2m0-8c2.21 0 4 .895 4 2m-4-6v2m0 12v2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span x-show="open">Expenses</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div x-show="showTooltip && !open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                        Expenses
                    </div>
                </a>
                <!-- Users (admin and manager can see) -->
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
                    <a href="/users" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-blue-800 transition {{ request()->is('users') ? 'bg-blue-800' : '' }}"
                       x-data="{ showTooltip: false }"
                       @mouseenter="if (!open) showTooltip = true"
                       @mouseleave="showTooltip = false">
                        <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 4a4 4 0 10-8 0 4 4 0 008 0z" />
                        </svg>
                            <span x-show="open">Users</span>
                        </span>
                        <svg x-show="open" class="h-4 w-4 text-white opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <!-- Tooltip -->
                        <div x-show="showTooltip && !open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded shadow-lg z-50 whitespace-nowrap">
                            Users
                        </div>
                    </a>
                @endif
            </nav>
        </aside>
    </div>