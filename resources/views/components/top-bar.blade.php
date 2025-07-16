<nav class="flex items-center justify-between px-8 py-5 bg-gradient-to-r from-indigo-500 via-blue-500 to-purple-500 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 shadow-lg rounded-b-2xl">
    <div class="flex items-center space-x-6">
        <span class="text-3xl tracking-wide font-extrabold text-white drop-shadow-lg">{{ config('app.name', 'Laravel Dashboard') }}</span>
    </div>
    <div class="flex items-center space-x-6">
        <!-- Theme Toggle -->
        <button x-data x-init="$watch('dark', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" @click="dark = !dark; if(dark){document.documentElement.classList.add('dark')}else{document.documentElement.classList.remove('dark')}" class="focus:outline-none transition-transform duration-200 hover:scale-110" :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'">
            <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-13.66l-.71.71M4.05 19.95l-.71.71M21 12h-1M4 12H3m16.66 5.66l-.71-.71M4.05 4.05l-.71-.71M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z" />
            </svg>
        </button>
        @auth
            <div class="flex items-center space-x-3 bg-white/10 px-4 py-2 rounded-full shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-white font-semibold">{{ Auth::user()->name ?? Auth::user()->email }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="ml-4 text-sm text-white font-semibold hover:underline hover:text-yellow-200 transition">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="text-sm text-white font-semibold hover:underline hover:text-yellow-200 transition">Log in</a>
            <a href="{{ route('register') }}" class="ml-4 text-sm text-white font-semibold hover:underline hover:text-yellow-200 transition">Register</a>
        @endauth
    </div>
</nav>
