<x-admin-layout>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Analytics Dashboard</h1>
            <div>
                <form action="{{ route('admin.analytics') }}" method="GET" class="flex gap-2">
                    <select name="period" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                        onchange="this.form.submit()">
                        <option value="7" {{ $period == 7 ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $period == 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ $period == 90 ? 'selected' : '' }}>Last 90 days</option>
                    </select>
                </form>
            </div>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-admin.metric-card
            label="Total Surveys"
            :value="$stats['total_surveys']"
            :change="$stats['new_surveys']"
            bgColor="bg-blue-100"
            :icon="'<svg class=\'h-6 w-6 text-blue-600\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2\' /></svg>'"
        />

        <x-admin.metric-card
            label="Total Responses"
            :value="$stats['total_responses']"
            :change="$stats['new_responses']"
            bgColor="bg-green-100"
            :icon="'<svg class=\'h-6 w-6 text-green-600\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z\' /></svg>'"
        />

        <x-admin.metric-card
            label="Total Users"
            :value="$stats['total_users']"
            :change="$stats['new_users']"
            bgColor="bg-purple-100"
            :icon="'<svg class=\'h-6 w-6 text-purple-600\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\' /></svg>'"
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Response Trends -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Response Trends</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="responseChart"></canvas>
            </div>
        </div>

        <!-- User Engagement -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">User Engagement</h3>
            <dl class="grid grid-cols-1 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Active Users</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $userEngagement['active_users'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Average Responses per User</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $userEngagement['avg_responses'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Average Completion Time</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $userEngagement['avg_completion_time'] }} minutes</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Survey Completion Rates -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top Survey Completion Rates</h3>
            <div class="space-y-4">
                @foreach($completionRates as $survey)
                    <div>
                        <div class="flex justify-between text-sm font-medium text-gray-900">
                            <span>{{ Str::limit($survey['title'], 30) }}</span>
                            <span>{{ $survey['rate'] }}%</span>
                        </div>
                        <div class="mt-2 relative pt-1">
                            <div class="overflow-hidden h-2 text-xs flex rounded bg-blue-100">
                                <div style="width: {{ $survey['rate'] }}%" class="bg-blue-500"></div>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            {{ $survey['completed'] }} / {{ $survey['total'] }} responses
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Popular Surveys -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Most Popular Surveys</h3>
            <div class="space-y-4">
                @foreach($popularSurveys as $survey)
                    <div class="flex justify-between items-start">
                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($survey['name'], 30) }}</div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ $survey['responses'] }} responses
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('responseChart').getContext('2d');
        const data = @json($responseTrends);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.date),
                datasets: [{
                    label: 'Responses',
                    data: data.map(item => item.count),
                    borderColor: '#3B82F6',
                    backgroundColor: '#93C5FD',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>