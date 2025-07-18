<nav class="flex items-center justify-between px-8 py-5 bg-gradient-to-r from-indigo-500 via-blue-500 to-purple-500 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 shadow-lg rounded-b-2xl">
    <div class="flex items-center space-x-6">
        <a href="{{ route('welcome') }}" class="text-3xl tracking-wide font-extrabold text-white drop-shadow-lg hover:text-yellow-200 transition">{{ config('app.name', 'Laravel Dashboard') }}</a>
    </div>
    
    <!-- Navigation Links -->
    <div class="hidden md:flex items-center space-x-6">
        @auth
            @if(Auth::user()->isAdmin())
                <!-- Admin Navigation -->
                <a href="{{ route('admin.dashboard') }}" class="text-white font-semibold hover:text-yellow-200 transition flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="text-white font-semibold hover:text-yellow-200 transition flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    <span>Users</span>
                </a>
                <a href="{{ route('admin.surveys.index') }}" class="text-white font-semibold hover:text-yellow-200 transition flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Surveys</span>
                </a>
                <a href="{{ route('admin.analytics') }}" class="text-white font-semibold hover:text-yellow-200 transition flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Analytics</span>
                </a>
            @else
                <!-- Normal User Navigation -->
                <a href="{{ route('welcome') }}" class="text-white font-semibold hover:text-yellow-200 transition flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Home</span>
                </a>
                <a href="{{ route('survey.create') }}" class="text-white font-semibold hover:text-yellow-200 transition flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Create Survey</span>
                </a>
                <a href="/survey" class="text-white font-semibold hover:text-yellow-200 transition flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4" />
                    </svg>
                    <span>My Surveys</span>
                </a>
            @endif
        @else
            <a href="{{ route('welcome') }}" class="text-white font-semibold hover:text-yellow-200 transition flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Home</span>
            </a>
        @endauth
    </div>

    <div class="flex items-center space-x-4">
        <!-- Theme Toggle -->
        <button 
            @click="dark = !dark" 
            class="relative inline-flex items-center justify-center p-2 rounded-lg focus:outline-none transition-colors duration-200"
            :class="{'bg-gray-700': dark, 'bg-yellow-100': !dark}"
            :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'"
        >
            <svg 
                x-show="!dark" 
                class="w-6 h-6 text-yellow-500 transform transition-transform duration-500 rotate-0"
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg 
                x-show="dark" 
                class="w-6 h-6 text-yellow-300 transform transition-transform duration-500 rotate-180"
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        @auth
            <!-- User Profile Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-3 bg-white/10 px-4 py-2 rounded-full shadow hover:bg-white/20 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-white font-semibold">{{ Auth::user()->name ?? Auth::user()->email }}</span>
                    @if(Auth::user()->isAdmin())
                        <span class="bg-yellow-500 text-black text-xs px-2 py-1 rounded-full">Admin</span>
                    @endif
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50" style="display: none;">
                    <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">
                        <p class="font-semibold">{{ Auth::user()->name ?? Auth::user()->email }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        @else
            <a href="{{ route('login') }}" class="text-sm text-white font-semibold hover:underline hover:text-yellow-200 transition">Log in</a>
            <a href="{{ route('register') }}" class="ml-4 text-sm text-white font-semibold hover:underline hover:text-yellow-200 transition">Register</a>
        @endauth

        <!-- Mobile Menu Toggle -->
        <div class="md:hidden">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-white p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Menu -->
<div x-data="{ mobileMenuOpen: false }" x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="md:hidden bg-indigo-600 dark:bg-gray-800 shadow-lg">
    <div class="px-4 py-3 space-y-2">
        @auth
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">Dashboard</a>
                <a href="{{ route('admin.users.index') }}" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">Users</a>
                <a href="{{ route('admin.surveys.index') }}" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">Surveys</a>
                <a href="{{ route('admin.analytics') }}" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">Analytics</a>
            @else
                <a href="{{ route('welcome') }}" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">Home</a>
                <a href="{{ route('survey.create') }}" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">Create Survey</a>
                <a href="/survey" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">My Surveys</a>
            @endif
        @else
            <a href="{{ route('welcome') }}" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">Home</a>
            <a href="{{ route('login') }}" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">Log in</a>
            <a href="{{ route('register') }}" class="block text-white font-semibold py-2 px-4 rounded hover:bg-indigo-700 dark:hover:bg-gray-700 transition">Register</a>
        @endauth
    </div>
</div>
