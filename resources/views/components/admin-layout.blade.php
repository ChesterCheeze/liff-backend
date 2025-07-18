<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
    x-init="$watch('dark', val => {
        localStorage.theme = val ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', val);
    });
    if (dark) document.documentElement.classList.add('dark');">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .dark .dark\:bg-gray-900 { background-color: #111827 !important; }
        .dark .dark\:text-white { color: #fff !important; }
        .dark .dark\:bg-gray-800 { background-color: #1f2937 !important; }
        .dark .dark\:border-gray-700 { border-color: #374151 !important; }
        .dark .dark\:hover\:bg-gray-700:hover { background-color: #374151 !important; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <x-top-bar />

    <div class="container mx-auto p-6">
        {{ $slot }}
    </div>
</body>
</html>