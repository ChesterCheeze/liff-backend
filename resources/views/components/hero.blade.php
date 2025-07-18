<div class="relative flex flex-col items-center justify-center min-h-[60vh] py-20 bg-gradient-to-br from-blue-100 via-indigo-100 to-purple-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 rounded-3xl shadow-lg overflow-hidden">
    <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 dark:text-white mb-6 drop-shadow-lg">Welcome to {{ config('app.name', 'Laravel Dashboard') }}</h1>
    <p class="text-xl md:text-2xl text-gray-700 dark:text-gray-300 mb-10 max-w-2xl mx-auto">Create and manage surveys with ease. Please log in or register to get started.</p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('login') }}" class="inline-block px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-lg font-semibold rounded-full shadow-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200">Log in</a>
        <a href="{{ route('register') }}" class="inline-block px-8 py-3 bg-white text-gray-900 text-lg font-semibold rounded-full shadow-lg border border-gray-200 hover:bg-gray-100 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600 transition-all duration-200">Register</a>
    </div>
</div>
