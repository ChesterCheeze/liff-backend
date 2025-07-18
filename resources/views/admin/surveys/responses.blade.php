<x-admin-layout>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Survey Responses</h1>
                <p class="text-gray-600">{{ $survey->title }}</p>
            </div>
            <a href="{{ route('admin.surveys.show', $survey) }}" 
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Back to Survey
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Respondent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($responses as $response)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $response->lineOaUser ? $response->lineOaUser->name : 'Unknown User' }}
                            </div>
                            <div class="text-sm text-gray-500">Line ID: {{ $response->line_id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $response->completed_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $response->completed_at ? 'Completed' : 'In Progress' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $response->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $response->completed_at ? $response->completed_at->format('M d, Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($response->completed_at)
                                {{ $response->completed_at->diffInMinutes($response->created_at) }} minutes
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button 
                                class="text-blue-600 hover:text-blue-900"
                                onclick="toggleResponses({{ $response->id }})">
                                View Answers
                            </button>
                        </td>
                    </tr>
                    <tr id="response-{{ $response->id }}" class="hidden bg-gray-50">
                        <td colspan="6" class="px-6 py-4">
                            <div class="space-y-4">
                                @if($response->answers)
                                    @foreach(json_decode($response->answers, true) as $questionId => $answer)
                                        <div>
                                            <div class="font-medium text-gray-700">
                                                {{ $survey->questions->find($questionId)->question_text ?? 'Question not found' }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                @if(is_array($answer))
                                                    <ul class="list-disc list-inside">
                                                        @foreach($answer as $item)
                                                            <li>{{ $item }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    {{ $answer }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-500">No answers recorded</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $responses->links() }}
        </div>
    </div>

    <script>
        function toggleResponses(responseId) {
            const row = document.getElementById(`response-${responseId}`);
            row.classList.toggle('hidden');
        }
    </script>
</x-admin-layout>