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
    </body>
</html>
