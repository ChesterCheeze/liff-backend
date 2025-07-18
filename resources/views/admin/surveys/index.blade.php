<x-admin-layout>
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Survey Management</h1>
        <div class="flex gap-4">
            <form action="{{ route('admin.surveys.index') }}" method="GET" class="flex gap-4">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search surveys..." 
                    value="{{ request('search') }}"
                    class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                >
                <select 
                    name="status" 
                    class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                >
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Filter
                </button>
            </form>
            <a href="{{ route('survey.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Create Survey
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responses</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($surveys as $survey)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $survey->name }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($survey->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            System
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $survey->status === 'published' ? 'bg-green-100 text-green-800' : 
                                   ($survey->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($survey->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $survey->responses_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $survey->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('admin.surveys.show', $survey) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            <a href="{{ route('admin.surveys.responses', $survey) }}" class="text-green-600 hover:text-green-900">Responses</a>
                            <form action="{{ route('admin.surveys.destroy', $survey) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 ml-4" 
                                    onclick="return confirm('Are you sure you want to delete this survey? This will also delete all responses.')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $surveys->links() }}
        </div>
    </div>
</x-admin-layout>