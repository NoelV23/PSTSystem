<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/rvj-logo2.png') }}">

        <style>
            .loading-skeleton {
                color: transparent !important;
                background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%);
                background-size: 200% 100%;
                animation: shimmer 1.2s infinite;
                border-radius: 4px;
                display: inline-block;
            }

            @keyframes shimmer {
                0% { background-position: -200% 0; }
                100% { background-position: 200% 0; }
            }
        </style>
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 min-h-screen">
        <x-navbar />
        <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">
            <!-- Sidebar -->
            <div>
                <x-sidebar x-model="sidebarOpen" />
            </div>
            <!-- Main Content -->
            <main :class="sidebarOpen ? 'ml-64' : 'ml-16'" class="flex-1 p-2 transition-all duration-200 flex flex-col">
                <div class="flex-1">
                    @yield('content')
                </div>
                <x-footer />
            </main>
        </div>
        
        <!-- Include Expense Reminder Modal -->
        @include('components.expense-reminder-modal')
        <script>
            // Listen for sidebar toggle events and update sidebarOpen
            document.addEventListener('alpine:init', () => {
                Alpine.data('sidebarState', () => ({
                    open: true,
                    toggle() {
                        this.open = !this.open;
                        window.dispatchEvent(new CustomEvent('sidebar-toggled', { detail: this.open }));
                    }
                }));
            });
            document.addEventListener('DOMContentLoaded', function() {
                window.addEventListener('sidebar-toggled', function(e) {
                    const main = document.querySelector('main');
                    if (main) {
                        main.classList.remove('ml-64', 'ml-16');
                        main.classList.add(e.detail ? 'ml-64' : 'ml-16');
                    }
                });
            });

            // Logout with expense check for managers
            async function handleLogout() {
                const userRole = '{{ auth()->user()->role ?? '' }}';
                
                if (userRole === 'manager') {
                    try {
                        const response = await fetch('/api/expenses/check-today');
                        const data = await response.json();
                        
                        if (!data.has_expense) {
                            // Show modal asking if they want to record expenses first
                            document.getElementById('expenseReminderModal').classList.remove('hidden');
                            return;
                        }
                    } catch (error) {
                        console.error('Error checking expense status:', error);
                    }
                }
                
                // Proceed with logout - try different form IDs
                const logoutForm = document.getElementById('logoutForm') || 
                                 document.getElementById('logoutFormNav') || 
                                 document.getElementById('logoutFormResponsive');
                if (logoutForm) {
                    logoutForm.submit();
                }
            }

            // Handle modal buttons
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('expenseReminderModal');
                const recordBtn = document.getElementById('recordExpensesBtn');
                const laterBtn = document.getElementById('maybeLaterBtn');

                if (recordBtn) {
                    recordBtn.addEventListener('click', function() {
                        window.location.href = '/expenses';
                    });
                }

                if (laterBtn) {
                    laterBtn.addEventListener('click', function() {
                        modal.classList.add('hidden');
                        const logoutForm = document.getElementById('logoutForm') || 
                                         document.getElementById('logoutFormNav') || 
                                         document.getElementById('logoutFormResponsive');
                        if (logoutForm) {
                            logoutForm.submit();
                        }
                    });
                }

                // Close modal when clicking outside
                modal?.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                    }
                });
            });
        </script>
        <script>
            // Disable submit buttons on any form submit and show a loading state to prevent double submissions
            document.addEventListener('DOMContentLoaded', function() {
                function handleSubmitDisable(e) {
                    const form = e.target;
                    if (!(form instanceof HTMLFormElement)) return;
                    
                    // Check if this form has custom submit handling (like sales forms)
                    const hasCustomSubmit = form.hasAttribute('data-custom-submit') || 
                                         form.id === 'addSaleForm' || 
                                         form.id === 'deliveryDetailsForm' || 
                                         form.id === 'addInstallationSaleForm';
                    
                    if (hasCustomSubmit) {
                        // For custom submit forms, just disable the button temporarily
                        const submits = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                        submits.forEach(function(btn) {
                            if (btn.disabled) return;
                            btn.disabled = true;
                            btn.classList.add('opacity-50', 'cursor-not-allowed');
                            if (btn.tagName.toLowerCase() === 'button') {
                                const loadingText = btn.getAttribute('data-loading-text') || 'Processing...';
                                btn.dataset.originalText = btn.innerHTML;
                                btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>' + '<span>' + loadingText + '</span>';
                            }
                        });
                        
                        // Re-enable after a short delay to allow custom logic to handle
                        setTimeout(() => {
                            submits.forEach(function(btn) {
                                btn.disabled = false;
                                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                                if (btn.dataset.originalText) {
                                    btn.innerHTML = btn.dataset.originalText;
                                    delete btn.dataset.originalText;
                                }
                            });
                        }, 100);
                        return;
                    }
                    
                    // For regular forms, keep the button disabled
                    const submits = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                    submits.forEach(function(btn) {
                        if (btn.disabled) return;
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                        if (btn.tagName.toLowerCase() === 'button') {
                            const loadingText = btn.getAttribute('data-loading-text') || 'Processing...';
                            btn.dataset.originalText = btn.innerHTML;
                            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>' + '<span>' + loadingText + '</span>';
                        }
                    });
                }
                document.querySelectorAll('form').forEach(function(form) {
                    form.addEventListener('submit', handleSubmitDisable, { capture: true });
                });
            });
        </script>
    </body>
</html>
