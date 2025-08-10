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
    <body class="font-sans text-gray-900 antialiased bg-white">
        <div class="min-h-screen flex flex-col items-center justify-center bg-white">
            {{ $slot }}
        </div>
        <script>
            // Disable submit buttons on guest forms and show a loading state
            document.addEventListener('DOMContentLoaded', function() {
                function handleSubmitDisable(e) {
                    const form = e.target;
                    if (!(form instanceof HTMLFormElement)) return;
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
