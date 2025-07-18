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
    public function dashboard()
    {
        $this->requireAdmin();

        $totalSurveys = Survey::count();
        $activeSurveys = Survey::where('status', 'active')->count();
        $totalResponses = SurveyResponse::count();
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

        return $this->successResponse([
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
        $responses = $survey->responses()
            ->with('lineoauser')
            ->orderBy('completed_at', 'desc')
            ->paginate($perPage);

        return $this->paginatedResponse($responses, 'Survey responses retrieved successfully');
    }

    public function surveyStats(Survey $survey)
    {
        $this->requireAdmin();

        $totalResponses = $survey->responses()->count();
        $completionRate = $survey->questions()->count() > 0 ?
            ($totalResponses / LineOAUser::count()) * 100 : 0;

        $responsesByDay = $survey->responses()
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
            ->where('completed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $avgCompletionTime = $survey->responses()
            ->whereNotNull('completed_at')
            ->avg('completion_time_minutes') ?? 0;

        return $this->successResponse([
            'survey_id' => $survey->id,
            'survey_name' => $survey->name,
            'total_responses' => $totalResponses,
            'completion_rate' => round($completionRate, 2),
            'avg_completion_time' => round($avgCompletionTime, 2),
            'responses_by_day' => $responsesByDay,
            'total_questions' => $survey->questions()->count(),
        ], 'Survey statistics retrieved successfully');
    }

    public function export(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'type' => 'required|in:surveys,responses,users',
            'format' => 'required|in:csv,json',
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
        ], 'Export initiated successfully');
    }
}
