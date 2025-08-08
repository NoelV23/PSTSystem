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
                        x-show="open"
                        class="flex flex-row items-center justify-center h-16"
                        style="margin-top: -18px; text-shadow: 1px 1px 0 black, -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black;"
                    >
                        <span class="text-3xl font-extrabold text-yellow-300 leading-tight tracking-tight" style="line-height: 1;">
                            RV
                        </span>
                        <span class="flex flex-col justify-center ml-3 h-full">
                            <span class="text-yellow-400 text-sm font-semibold leading-none" style="line-height: 1.1;">
                                GLASS <span class="text-yellow-300">and</span>
                            </span>
                            <span class="text-yellow-400 text-xs font-semibold uppercase leading-none" style="line-height: 1.1;">
                                ALUMINUM SUPPLY
                            </span>
                        </span>
                    </span>

                    <span 
                        class="text-3xl font-extrabold text-yellow-300 leading-tight tracking-tight text-center mb-4" 
                        x-show="!open"
                        style="margin-top: -6px; text-shadow: 1px 1px 0 black, -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black;">
                        RV
                    </span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="flex-1 flex flex-col gap-2 mt-3">
                <!-- Dashboard, hidden if user is staff -->
                @if(auth()->user()->role !== 'staff')
                <a href="/dashboard" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('dashboard') ? 'bg-red-700' : '' }}" 
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
                <a href="/sales" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('sales') ? 'bg-red-700' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24.00 24.00">
                            <path d="M9 15L15 9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"></path>
                            <path d="M15.5 14.5C15.5 15.0523 15.0523 15.5 14.5 15.5C13.9477 15.5 13.5 15.0523 13.5 14.5C13.5 13.9477 13.9477 13.5 14.5 13.5C15.0523 13.5 15.5 13.9477 15.5 14.5Z" fill="currentColor"></path>
                            <path d="M10.5 9.5C10.5 10.0523 10.0523 10.5 9.5 10.5C8.94772 10.5 8.5 10.0523 8.5 9.5C8.5 8.94772 8.94772 8.5 9.5 8.5C10.0523 8.5 10.5 8.94772 10.5 9.5Z" fill="currentColor"></path>
                            <path d="M22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C21.5093 4.43821 21.8356 5.80655 21.9449 8" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"></path>
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

                <!-- Purchases -->
                <a href="/purchases" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('purchases') ? 'bg-red-700' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 209.163 209.163">
                            <path d="M155.214,60.485c-0.62,2.206-2.627,3.649-4.811,3.649c-0.447,0-0.902-0.061-1.355-0.188l-40.029-11.241 c-2.659-0.747-4.209-3.507-3.462-6.166c0.747-2.658,3.506-4.209,6.166-3.462l40.03,11.241 C154.41,55.066,155.961,57.826,155.214,60.485z M84.142,182.268c-7.415,0-13.448,6.033-13.448,13.448 c0,7.415,6.033,13.447,13.448,13.447c7.415,0,13.447-6.032,13.447-13.447C97.589,188.301,91.557,182.268,84.142,182.268z M165.761,182.268c-7.415,0-13.448,6.033-13.448,13.448c0,7.415,6.033,13.447,13.448,13.447c7.415,0,13.448-6.032,13.448-13.447 C179.208,188.301,173.176,182.268,165.761,182.268z M197.442,72.788l-12.996,71.041c-0.435,2.375-2.504,4.1-4.918,4.1H72.198 l2.76,13.012c0.686,3.233,3.583,5.58,6.888,5.58h90.751c2.761,0,5,2.239,5,5s-2.239,5-5,5H81.845c-7.999,0-15.01-5.68-16.67-13.505 l-4.024-18.97L34.382,35.294H16.639c-2.761,0-5-2.239-5-5c0-2.761,2.239-5,5-5H38.3c2.301,0,4.305,1.57,4.855,3.805l9.265,37.639 l29.969,0.032l13.687-48.737c0.001-0.002,0-0.003,0.001-0.005l4.038-14.376c0.747-2.658,3.507-4.21,6.166-3.462l72.448,20.344 c2.659,0.747,4.209,3.507,3.462,6.165c-0.62,2.207-2.627,3.649-4.811,3.65c-0.447,0-0.902-0.06-1.354-0.188l-1.106-0.311 l-1.294,4.608l1.106,0.31c2.658,0.747,4.208,3.507,3.462,6.166l-7.282,25.93l21.62,0.023c1.482,0.001,2.888,0.661,3.837,1.8 C197.315,69.828,197.709,71.329,197.442,72.788z M108.389,11.168l-1.294,4.608l56.9,15.979l1.294-4.608L108.389,11.168z M95.31,66.783l63.083,0.068l3.061-10.899c0.358-1.277,0.195-2.644-0.454-3.8c-0.649-1.157-1.731-2.007-3.008-2.366L109.13,36.065 c-1.276-0.359-2.643-0.196-3.8,0.454c-1.156,0.649-2.007,1.731-2.366,3.008L95.31,66.783z"></path>
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
                <a href="/inventory" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('inventory') ? 'bg-red-700' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <defs>
                                <path d="M19.6554561,20.9177505 L20.1324211,21.5082919 C19.7289347,21.8246698 19.2287757,22 18.7024516,22 C18.5957479,22 18.4898379,21.9928085 18.385261,21.9785596 L18.4901851,21.2309569 C18.5600191,21.240472 18.6308721,21.245283 18.7024516,21.245283 C19.0539479,21.245283 19.3863488,21.1287603 19.6554561,20.9177505 Z M13.2918622,17.3791385 C14.5579751,17.3791385 15.5843624,18.3904426 15.5843624,19.6379494 C15.5843624,20.8854563 14.5579751,21.8967604 13.2918622,21.8967604 C12.0257494,21.8967604 10.9993621,20.8854563 10.9993621,19.6379494 C10.9993621,18.3904426 12.0257494,17.3791385 13.2918622,17.3791385 Z M17.2015818,20.040198 C17.2869873,20.451794 17.5449795,20.8093683 17.9081786,21.0268796 L17.510337,21.6718118 C16.9661345,21.3459018 16.579504,20.8100362 16.4511322,20.1913721 L17.2015818,20.040198 Z M10.3971131,2 C11.5991439,2 12.616247,2.76528302 13.0045954,3.82210243 L13.0045954,3.82210243 L15.9449479,3.82210243 C16.9620509,3.82210243 17.7942261,4.64204852 17.7942261,5.64420485 L17.7942261,5.64420485 L17.7942261,11.1105121 L15.9449479,11.1105121 L15.9449479,5.64420485 L14.0956696,5.64420485 L14.0956696,8.37735849 L6.69855654,8.37735849 L6.69855654,5.64420485 L4.84927827,5.64420485 L4.84927827,19.309973 L9.47247394,19.309973 L9.47247394,21.1320755 L4.84927827,21.1320755 C3.83217522,21.1320755 3,20.3121294 3,19.309973 L3,19.309973 L3,5.64420485 C3,4.64204852 3.83217522,3.82210243 4.84927827,3.82210243 L4.84927827,3.82210243 L7.78963072,3.82210243 C8.17797915,2.76528302 9.1950822,2 10.3971131,2 Z M20.8598019,18.9546151 C20.9472125,19.1888929 20.9948768,19.436931 20.9999984,19.6977675 C21.0004225,20.0933426 20.9155279,20.4469131 20.7476245,20.7690374 L20.0662579,20.4242448 C20.1778966,20.2100648 20.2343216,19.9750652 20.2340973,19.7055583 C20.2307241,19.5358936 20.1990081,19.370848 20.1408704,19.2150275 L20.8598019,18.9546151 Z M17.5210599,17.793504 L17.9153266,18.440564 C17.550937,18.6561162 17.2909665,19.0123847 17.203282,19.4234195 L16.4536885,19.2681768 C16.5854849,18.6503604 16.9750506,18.1164922 17.5210599,17.793504 Z M18.7024516,17.4716981 C19.350097,17.4716981 19.9554053,17.7375693 20.3872523,18.1961485 L19.8257551,18.7094874 C19.5373774,18.4032585 19.1347584,18.2264151 18.7024516,18.2264151 C18.6390357,18.2269825 18.5619322,18.2313948 18.4998951,18.2394494 L18.3998271,17.4912006 C18.4927197,17.4791399 18.6078739,17.4725444 18.7024516,17.4716981 Z M18.5597349,12.1886792 C19.8258477,12.1886792 20.852235,13.1999834 20.852235,14.4474902 C20.852235,15.6949971 19.8258477,16.7063012 18.5597349,16.7063012 C17.293622,16.7063012 16.2672347,15.6949971 16.2672347,14.4474902 C16.2672347,13.1999834 17.293622,12.1886792 18.5597349,12.1886792 Z M13.2918622,12.1886792 C14.5579751,12.1886792 15.5843624,13.1999834 15.5843624,14.4474902 C15.5843624,15.6949971 14.5579751,16.7063012 13.2918622,16.7063012 C12.0257494,16.7063012 10.9993621,15.6949971 10.9993621,14.4474902 C10.9993621,13.1999834 12.0257494,12.1886792 13.2918622,12.1886792 Z M10.3971131,3.82210243 C9.88856155,3.82210243 9.47247394,4.23207547 9.47247394,4.73315364 C9.47247394,5.23423181 9.88856155,5.64420485 10.3971131,5.64420485 C10.9056646,5.64420485 11.3217522,5.23423181 11.3217522,4.73315364 C11.3217522,4.23207547 10.9056646,3.82210243 10.3971131,3.82210243 Z" id="path-1"> </path> </defs>
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <mask id="mask-2" fill="white">
                                    <use xlink:href="#path-1"> </use>
                                </mask>
                                <use fill="currentColor" fill-rule="nonzero" xlink:href="#path-1"> </use>
                            </g>
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

                <!-- Reports (not accessible by staff) -->
                @if(auth()->user()->role !== 'staff')
                <a href="/reports" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('reports*') ? 'bg-red-700' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 4h6a2 2 0 002-2v-5a2 2 0 00-2-2h-6a2 2 0 00-2 2v5a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
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
                <a href="/stock-adjustments" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('stock-adjustments*') ? 'bg-red-700' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 6v6m0 0v6m0-6h6m-6 0H6" stroke-linecap="round" stroke-linejoin="round"/>
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
                <a href="/products" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('products') ? 'bg-red-700' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 512 512">
                            <path d="M426.247658,366.986259 C426.477599,368.072636 426.613335,369.17172 426.653805,370.281095 L426.666667,370.986667 L426.666667,392.32 C426.666667,415.884149 383.686003,434.986667 330.666667,434.986667 C278.177524,434.986667 235.527284,416.264289 234.679528,393.025571 L234.666667,392.32 L234.666667,370.986667 L234.679528,370.281095 C234.719905,369.174279 234.855108,368.077708 235.081684,366.992917 C240.961696,371.41162 248.119437,375.487081 256.413327,378.976167 C275.772109,387.120048 301.875889,392.32 330.666667,392.32 C360.599038,392.32 387.623237,386.691188 407.213205,377.984536 C414.535528,374.73017 420.909655,371.002541 426.247658,366.986259 Z M192,7.10542736e-15 L384,106.666667 L384.001134,185.388691 C368.274441,181.351277 350.081492,178.986667 330.666667,178.986667 C301.427978,178.986667 274.9627,184.361969 255.43909,193.039129 C228.705759,204.92061 215.096345,223.091357 213.375754,241.480019 L213.327253,242.037312 L213.449,414.75 L192,426.666667 L-2.13162821e-14,320 L-2.13162821e-14,106.666667 L192,7.10542736e-15 Z M426.247658,302.986259 C426.477599,304.072636 426.613335,305.17172 426.653805,306.281095 L426.666667,306.986667 L426.666667,328.32 C426.666667,351.884149 383.686003,370.986667 330.666667,370.986667 C278.177524,370.986667 235.527284,352.264289 234.679528,329.025571 L234.666667,328.32 L234.666667,306.986667 L234.679528,306.281095 C234.719905,305.174279 234.855108,304.077708 235.081684,302.992917 C240.961696,307.41162 248.119437,311.487081 256.413327,314.976167 C275.772109,323.120048 301.875889,328.32 330.666667,328.32 C360.599038,328.32 387.623237,322.691188 407.213205,313.984536 C414.535528,310.73017 420.909655,307.002541 426.247658,302.986259 Z M127.999,199.108 L128,343.706 L170.666667,367.410315 L170.666667,222.811016 L127.999,199.108 Z M42.6666667,151.701991 L42.6666667,296.296296 L85.333,320.001 L85.333,175.405 L42.6666667,151.701991 Z M330.666667,200.32 C383.155809,200.32 425.80605,219.042377 426.653805,242.281095 L426.666667,242.986667 L426.666667,264.32 C426.666667,287.884149 383.686003,306.986667 330.666667,306.986667 C278.177524,306.986667 235.527284,288.264289 234.679528,265.025571 L234.666667,264.32 L234.666667,242.986667 L234.808715,240.645666 C237.543198,218.170241 279.414642,200.32 330.666667,200.32 Z M275.991,94.069 L150.412,164.155 L192,187.259259 L317.866667,117.333333 L275.991,94.069 Z M192,47.4074074 L66.1333333,117.333333 L107.795,140.479 L233.373,70.393 L192,47.4074074 Z"></path>
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
                <a href="/branches" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('branches') ? 'bg-red-700' : '' }}"
                   x-data="{ showTooltip: false }"
                   @mouseenter="if (!open) showTooltip = true"
                   @mouseleave="showTooltip = false">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="-0.23 0 16 16">
                            <path d="M-12.5-96h-7A1.5,1.5,0,0,0-21-94.5v3a.5.5,0,0,0,.5.5.5.5,0,0,0,.5-.5v-3a.5.5,0,0,1,.5-.5h7a.5.5,0,0,1,.5.5V-81h-2v-3.5a.5.5,0,0,0-.5-.5h-3a.5.5,0,0,0-.5.5V-81h-1.5a.5.5,0,0,0-.5.5.5.5,0,0,0,.5.5H-11V-94.5A1.5,1.5,0,0,0-12.5-96ZM-17-81v-3h2v3Zm0-10h-2v-2h2Zm4,0h-2v-2h2Zm-2,2h2v2h-2Zm-3.755,2H-17v-2h-2v1.526a4.023,4.023,0,0,0-.646-.88,4.042,4.042,0,0,0-5.708,0,4.042,4.042,0,0,0,0,5.708l2.5,2.5A.5.5,0,0,0-22.5-80a.5.5,0,0,0,.354-.146l2.5-2.5A4.041,4.041,0,0,0-18.755-87ZM-22.5-81.207l-2.146-2.147a3.037,3.037,0,0,1,0-4.292,3.024,3.024,0,0,1,2.146-.888,3.024,3.024,0,0,1,2.146.888,3.037,3.037,0,0,1,0,4.292ZM-21-85.5A1.5,1.5,0,0,1-22.5-84,1.5,1.5,0,0,1-24-85.5,1.5,1.5,0,0,1-22.5-87,1.5,1.5,0,0,1-21-85.5Z" transform="translate(26.534 96)"></path>
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
                <!-- Users (admin and manager can see) -->
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
                    <a href="/users" class="relative flex items-center justify-between py-3 px-2 rounded-lg text-white font-medium hover:bg-red-700 transition {{ request()->is('users') ? 'bg-red-700' : '' }}"
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