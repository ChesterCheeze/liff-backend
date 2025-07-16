<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" x-init="$watch('dark', val => localStorage.setItem('theme', val ? 'dark' : 'light')); if(localStorage.getItem('theme') === 'dark'){document.documentElement.classList.add('dark')}else{document.documentElement.classList.remove('dark')}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | {{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @vite('resources/css/app.css')
    <style>
        .dark .dark\:bg-gray-900 { background-color: #111827 !important; }
        .dark .dark\:text-white { color: #fff !important; }
        .dark .dark\:bg-gray-800 { background-color: #1f2937 !important; }
        .dark .dark\:border-gray-700 { border-color: #374151 !important; }
        .dark .dark\:hover\:bg-gray-700:hover { background-color: #374151 !important; }
    </style>
</head>
<body :class="{ 'dark': dark }" class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <!-- Top Bar -->
    <x-top-bar />

<main class="max-w-6xl mx-auto mt-14 px-4 sm:px-8">
    <x-flash-message />
    @auth
        <div class="mb-10 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 drop-shadow">Welcome back, {{ Auth::user()->name ?? Auth::user()->email }}!</h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">Here’s your dashboard. Manage your surveys and profile below.</p>
        </div>
        <x-dashboard />
    @else
        <x-hero />
    @endauth
</main>    <x-footer />
</body>
</html>
