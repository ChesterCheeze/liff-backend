<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Http\Request;

class AnalyticsController extends BaseApiController
{
    public function dashboard(Request $request)
    {
        $this->requireAdmin();

        // Handle date filtering
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($dateFrom || $dateTo) {
            $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);
        }

        $totalSurveys = Survey::count();
        $activeSurveys = Survey::where('status', 'active')->count();

        // Apply date filtering to responses if provided
        $responsesQuery = SurveyResponse::query();
        if ($dateFrom) {
            $responsesQuery->where('completed_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $responsesQuery->where('completed_at', '<=', $dateTo);
        }
        $totalResponses = $responsesQuery->count();

        $totalUsers = User::count();
        $totalLineUsers = LineOAUser::count();

        $recentResponses = SurveyResponse::where('completed_at', '>=', now()->subDays(7))->count();
        $recentSurveys = Survey::where('created_at', '>=', now()->subDays(7))->count();

        $responsesByDay = SurveyResponse::selectRaw('DATE(completed_at) as date, COUNT(*) as count')
            ->where('completed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topSurveys = Survey::withCount('responses')
            ->orderBy('responses_count', 'desc')
            ->take(5)
            ->get(['id', 'name', 'responses_count']);

        // User breakdown by type
        $regularUserResponses = SurveyResponse::where('user_type', 'App\\Models\\User')->count();
        $lineUserResponses = SurveyResponse::where('user_type', 'App\\Models\\LineOAUser')->count();

        return $this->successResponse([
            'total_surveys' => $totalSurveys,
            'total_responses' => $totalResponses,
            'total_users' => $totalUsers, // Just regular users, not including LINE users
            'recent_responses' => $recentResponses,
            'survey_stats' => $topSurveys->toArray(),
            'user_breakdown' => [
                'regular_users' => $regularUserResponses,
                'line_users' => $lineUserResponses,
            ],
            'overview' => [
                'total_surveys' => $totalSurveys,
                'active_surveys' => $activeSurveys,
                'total_responses' => $totalResponses,
                'total_users' => $totalUsers,
                'total_line_users' => $totalLineUsers,
            ],
            'recent_activity' => [
                'responses_last_7_days' => $recentResponses,
                'surveys_last_7_days' => $recentSurveys,
            ],
            'charts' => [
                'responses_by_day' => $responsesByDay,
                'top_surveys' => $topSurveys,
            ],
        ], 'Dashboard analytics retrieved successfully');
    }

    public function surveyResponses(Survey $survey)
    {
        $this->requireAdmin();

        $perPage = $this->getPerPage();
        $responsesQuery = $survey->responses()
            ->orderBy('completed_at', 'desc');

        $responses = $responsesQuery->paginate($perPage);

        // Transform the response data to match expected structure
        $transformedResponses = $responses->getCollection()->map(function ($response) {
            return [
                'id' => $response->id,
                'user_id' => $response->user_id,
                'user_type' => $response->user_type,
                'completed_at' => $response->completed_at?->toISOString(),
                'answers' => $response->form_data ?? [],
            ];
        });

        return $this->successResponse([
            'responses' => $transformedResponses->toArray(),
            'pagination' => [
                'current_page' => $responses->currentPage(),
                'total_pages' => $responses->lastPage(),
                'per_page' => $responses->perPage(),
                'total' => $responses->total(),
            ],
        ], 'Survey responses retrieved successfully');
    }

    public function surveyStats(Survey $survey)
    {
        $this->requireAdmin();

        $totalResponses = $survey->responses()->count();
        $totalPossibleResponses = User::count() + LineOAUser::count();
        $responseRate = $totalPossibleResponses > 0 ?
            ($totalResponses / $totalPossibleResponses) * 100 : 0;

        $responsesByDay = $survey->responses()
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
            ->where('completed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $avgCompletionTime = 0;
        $completedResponses = $survey->responses()
            ->whereNotNull('completed_at')
            ->get();

        if ($completedResponses->isNotEmpty()) {
            $totalMinutes = $completedResponses->sum(function ($response) {
                return $response->completed_at->diffInMinutes($response->created_at);
            });
            $avgCompletionTime = $totalMinutes / $completedResponses->count();
        }

        // User breakdown
        $regularUserResponses = $survey->responses()->where('user_type', 'App\\Models\\User')->count();
        $lineUserResponses = $survey->responses()->where('user_type', 'App\\Models\\LineOAUser')->count();

        // Question stats (basic example)
        $questions = $survey->questions;
        $questionStats = $questions->map(function ($question) use ($survey) {
            return [
                'question_id' => $question->id,
                'label' => $question->label,
                'type' => $question->type,
                'response_count' => $survey->responses()->count(), // Simplified
            ];
        });

        return $this->successResponse([
            'survey_id' => $survey->id,
            'survey_name' => $survey->name,
            'total_responses' => $totalResponses,
            'response_rate' => round($responseRate, 2),
            'avg_completion_time' => round($avgCompletionTime, 2),
            'user_breakdown' => [
                'regular_users' => $regularUserResponses,
                'line_users' => $lineUserResponses,
            ],
            'daily_responses' => $responsesByDay->toArray(),
            'question_stats' => $questionStats->toArray(),
            'total_questions' => $survey->questions()->count(),
        ], 'Survey statistics retrieved successfully');
    }

    public function export(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'format' => 'required|in:csv,json,pdf',
            'type' => 'nullable|in:surveys,responses,users',
            'include_charts' => 'boolean',
            'surveys' => 'array',
            'surveys.*' => 'exists:surveys,id',
            'survey_id' => 'nullable|exists:surveys,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        // This would typically generate and return a file download
        // For now, return a success message indicating export initiation
        return $this->successResponse([
            'export_id' => uniqid(),
            'status' => 'initiated',
            'estimated_completion' => now()->addMinutes(5)->toISOString(),
            'format' => $request->format,
            'type' => $request->type ?? 'analytics',
            'surveys_included' => count($request->surveys ?? []),
        ], 'Export initiated successfully');
    }
}
