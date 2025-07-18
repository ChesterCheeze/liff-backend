<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = Survey::query();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $surveys = $query->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.surveys.index', compact('surveys'));
    }

    public function show(Survey $survey)
    {
        $survey->load(['questions', 'responses']);
        $survey->loadCount('responses');

        $responseStats = [
            'total' => $survey->responses_count,
            'completion_rate' => $this->calculateCompletionRate($survey),
            'average_time' => $this->calculateAverageCompletionTime($survey),
        ];

        return view('admin.surveys.show', compact('survey', 'responseStats'));
    }

    public function responses(Survey $survey)
    {
        $responses = $survey->responses()
            ->with('lineOaUser')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.surveys.responses', compact('survey', 'responses'));
    }

    public function destroy(Survey $survey)
    {
        DB::transaction(function () use ($survey) {
            $survey->responses()->delete();
            $survey->questions()->delete();
            $survey->delete();
        });

        return redirect()
            ->route('admin.surveys.index')
            ->with('success', 'Survey deleted successfully');
    }

    private function calculateCompletionRate(Survey $survey)
    {
        if ($survey->responses_count === 0) {
            return 0;
        }

        $completedCount = $survey->responses()
            ->whereNotNull('completed_at')
            ->count();

        return round(($completedCount / $survey->responses_count) * 100);
    }

    private function calculateAverageCompletionTime(Survey $survey)
    {
        return $survey->responses()
            ->whereNotNull('completed_at')
            ->avg(DB::raw('TIMESTAMPDIFF(SECOND, created_at, completed_at)')) ?? 0;
    }
}
