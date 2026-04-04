    <div x-data="pstSidebar" class="relative h-full">
        <!-- Mobile: dim background when drawer open -->
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="open = false; window.dispatchEvent(new CustomEvent('sidebar-toggled', { detail: open }));"
            class="fixed inset-0 z-30 bg-slate-900/55 backdrop-blur-sm md:hidden"
            x-cloak
        ></div>

        <!-- Mobile only: open menu when drawer is closed (does not overlap sidebar logo) -->
        <button
            type="button"
            x-show="!open"
            x-cloak
            @click="toggleSidebar()"
            class="fixed bottom-6 left-4 z-[60] flex h-12 w-12 items-center justify-center rounded-xl bg-[#f4c20d] text-slate-900 shadow-lg shadow-amber-900/25 ring-2 ring-white/40 transition hover:bg-[#f5cc2f] focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-200 md:hidden"
            aria-label="Open menu"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <aside
            :class="open
                ? 'translate-x-0 w-[min(17.5rem,88vw)] md:w-64 px-2'
                : '-translate-x-full md:translate-x-0 md:w-16 w-[min(17.5rem,88vw)] px-2 md:px-0'"
            class="pst-scrollbar-none fixed left-0 top-0 z-40 flex h-screen min-h-0 flex-col overflow-y-auto overflow-x-hidden border-r border-white/10 bg-gradient-to-b from-[#071225] via-[#0a2d6a] to-[#0a2d9a] pb-6 pt-0 shadow-2xl shadow-black/25 transition-all duration-300 ease-in-out md:shadow-none"
        >
            <div
                class="flex shrink-0 flex-col gap-2 leading-none"
                :class="open ? 'px-0.5' : 'px-0.5 md:px-0'"
            >
                <a
                    href="{{ auth()->user()->role === 'staff' ? url('/sales') : url('/dashboard') }}"
                    class="block outline-none focus-visible:ring-2 focus-visible:ring-[#f4c20d] focus-visible:ring-offset-2 focus-visible:ring-offset-[#0a2d9a] rounded-lg md:rounded-none"
                >
                    <img
                        src="{{ asset('images/PSTLogoNoBG2.png') }}"
                        alt="Polytech Steel Trading"
                        :class="open
                            ? 'h-[8.5rem] sm:h-[9.5rem] md:h-[10.5rem] w-full max-w-full object-contain object-center'
                            : 'h-[3.5rem] w-full max-w-full object-contain object-center md:h-auto md:min-h-[4.5rem] md:max-h-[5.25rem]'"
                        class="block drop-shadow-[0_2px_10px_rgba(0,0,0,0.35)] transition-[height] duration-300"
                        width="280"
                        height="120"
                        decoding="async"
                    />
                </a>

                <!-- Toggle sits under the logo (never covers it) -->
                <button
                    type="button"
                    @click="toggleSidebar()"
                    class="mb-2 flex h-10 w-full shrink-0 items-center justify-center rounded-lg bg-[#f4c20d]/95 text-slate-900 shadow-md shadow-black/20 ring-1 ring-white/25 transition hover:bg-[#f5cc2f] focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-200 md:mb-3 md:rounded-none md:rounded-b-sm"
                    aria-label="Toggle sidebar"
                >
                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="mt-0 flex flex-1 flex-col gap-0.5">
                <!-- Dashboard, hidden if user is staff -->
                @if(auth()->user()->role !== 'staff')
                <a href="/dashboard" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('dashboard') || request()->is('/') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}" 
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
                <a href="/sales" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('sales') || request()->is('sales/*') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}"
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
                <a href="/purchases" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('purchases') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}"
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
                <a href="/inventory" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('inventory') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}"
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
                <a href="/reports" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('reports*') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}"
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
                <a href="/stock-adjustments" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('stock-adjustments*') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}"
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
                <a href="/products" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('products') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}"
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
                <a href="/branches" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('branches') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}"
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
                <a href="/expenses" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('expenses') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}"
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
                    <a href="/users" class="relative flex items-center justify-between py-2.5 px-2 rounded-lg text-white font-medium hover:bg-white/10 transition {{ request()->is('users') ? 'bg-white/15 ring-1 ring-[#f4c20d]/60 shadow-sm' : '' }}"
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