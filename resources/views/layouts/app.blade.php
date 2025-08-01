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
    <body class="font-sans antialiased bg-gray-50 min-h-screen flex flex-col">
        <x-navbar />
        <div x-data="{ sidebarOpen: false }" class="flex flex-1 min-h-0">
            <!-- Sidebar -->
            <div>
                <x-sidebar x-model="sidebarOpen" />
            </div>
            <!-- Main Content -->
            <main :class="sidebarOpen ? 'ml-64' : 'ml-16'" class="flex-1 p-2 transition-all duration-200">
                @yield('content')
            </main>
        </div>
        <x-footer />
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
        </script>
    </body>
</html>
