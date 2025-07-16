<a href="{{ $link }}" class="block p-8 bg-gradient-to-br from-white via-blue-50 to-indigo-100 dark:from-gray-800 dark:via-gray-900 dark:to-gray-800 rounded-2xl shadow-xl border border-transparent hover:border-blue-400 dark:hover:border-indigo-500 transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
    <div class="flex items-center mb-4">
        <span class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 dark:from-indigo-700 dark:to-blue-900 shadow-lg">
            {!! $icon !!}
        </span>
        <span class="ml-4 text-xl font-bold text-gray-800 dark:text-white">{{ $title }}</span>
    </div>
    <p class="text-gray-600 dark:text-gray-300 text-base">{{ $description }}</p>
</a>
