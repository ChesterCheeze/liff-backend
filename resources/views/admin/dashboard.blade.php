<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-admin.metric-card
            label="Total Users"
            :value="$totalUsers"
            :change="$userGrowth"
            bgColor="bg-blue-100"
            :icon="'<svg class=\'h-6 w-6 text-blue-600\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\' /></svg>'"
        />

        <x-admin.metric-card
            label="Total Surveys"
            :value="$totalSurveys"
            :change="$surveyGrowth"
            bgColor="bg-green-100"
            :icon="'<svg class=\'h-6 w-6 text-green-600\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2\' /></svg>'"
        />

        <x-admin.metric-card
            label="Total Responses"
            :value="$totalResponses"
            :change="$responseGrowth"
            bgColor="bg-purple-100"
            :icon="'<svg class=\'h-6 w-6 text-purple-600\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\' /></svg>'"
        />
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold mb-4">Recent Activity</h2>
        <div class="space-y-4">
            @foreach($recentActivity as $activity)
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <p class="font-medium">{{ $activity->user_name }}</p>
                        <p class="text-sm text-gray-500">responded to "{{ $activity->name }}"</p>
                    </div>
                    <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>