<x-admin-layout>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Survey Details: {{ $survey->title }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.surveys.responses', $survey) }}" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    View Responses
                </a>
                <form action="{{ route('admin.surveys.destroy', $survey) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                        onclick="return confirm('Are you sure you want to delete this survey?')">
                        Delete Survey
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Response Statistics</h3>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total Responses</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $responseStats['total'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Completion Rate</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $responseStats['completion_rate'] }}%</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Average Completion Time</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ $responseStats['average_time'] > 0 ? round($responseStats['average_time'] / 60, 1) : 0 }} minutes
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Survey Information</h3>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $survey->description }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $survey->status === 'published' ? 'bg-green-100 text-green-800' : 
                               ($survey->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ ucfirst($survey->status) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $survey->created_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Survey Questions</h3>
        <div class="space-y-6">
            @foreach($survey->questions as $index => $question)
                <div class="border-b pb-4 last:border-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-sm text-gray-500">Question {{ $index + 1 }}</span>
                            <p class="text-gray-900 font-medium">{{ $question->question_text }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                            {{ ucfirst($question->type) }}
                        </span>
                    </div>
                    @if($question->options)
                        <div class="mt-2">
                            <span class="text-sm text-gray-500">Options:</span>
                            <ul class="mt-1 list-disc list-inside text-sm text-gray-600">
                                @foreach(json_decode($question->options) as $option)
                                    <li>{{ $option }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>