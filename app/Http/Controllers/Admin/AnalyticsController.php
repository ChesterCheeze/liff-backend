<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{


    public function index(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        // Overall statistics
        $stats = $this->getOverallStats($startDate);

        // Response trends
        $responseTrends = $this->getResponseTrends($startDate);

        // Survey completion rates
        $completionRates = $this->getSurveyCompletionRates();

        // User engagement metrics
        $userEngagement = $this->getUserEngagementMetrics($startDate);

        // Popular surveys
        $popularSurveys = $this->getPopularSurveys();

        return view('admin.analytics.index', compact(
            'stats',
            'responseTrends',
            'completionRates',
            'userEngagement',
            'popularSurveys',
            'period'
        ));
    }

    private function getOverallStats($startDate)
    {
        return [
            'total_surveys' => Survey::count(),
            'total_responses' => SurveyResponse::count(),
            'total_users' => LineOAUser::where('role', 'user')->count(),
            'new_users' => LineOAUser::where('role', 'user')->where('created_at', '>=', $startDate)->count(),
            'new_surveys' => Survey::where('created_at', '>=', $startDate)->count(),
            'new_responses' => SurveyResponse::where('created_at', '>=', $startDate)->count(),
        ];
    }

    private function getResponseTrends($startDate)
    {
        return DB::table('survey_responses')
            ->select(DB::raw('DATE(survey_responses.created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('survey_responses.created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(survey_responses.created_at)'))
            ->orderBy(DB::raw('DATE(survey_responses.created_at)'))
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M d'),
                    'count' => $item->count,
                ];
            });
    }

    private function getSurveyCompletionRates()
    {
        return Survey::withCount(['responses', 'responses as completed_count' => function ($query) {
            $query->whereNotNull('completed_at');
        }])
            ->having('responses_count', '>', 0)
            ->get()
            ->map(function ($survey) {
                return [
                    'title' => $survey->name,
                    'total' => $survey->responses_count,
                    'completed' => $survey->completed_count,
                    'rate' => round(($survey->completed_count / $survey->responses_count) * 100),
                ];
            })
            ->sortByDesc('rate')
            ->values()
            ->take(5);
    }

    private function getUserEngagementMetrics($startDate)
    {
        $activeUsers = DB::table('survey_responses')
            ->select('survey_responses.line_id', DB::raw('COUNT(*) as response_count'))
            ->where('survey_responses.created_at', '>=', $startDate)
            ->join('lineoausers', 'survey_responses.line_id', '=', 'lineoausers.line_id')
            ->where('lineoausers.role', 'user')
            ->groupBy('survey_responses.line_id')
            ->having('response_count', '>', 0)
            ->count();

        $averageResponsesPerUser = DB::table('survey_responses')
            ->join('lineoausers', 'survey_responses.line_id', '=', 'lineoausers.line_id')
            ->where('lineoausers.role', 'user')
            ->where('survey_responses.created_at', '>=', $startDate)
            ->count() / max(1, $activeUsers);

        $completionTime = SurveyResponse::whereNotNull('completed_at')
            ->where('created_at', '>=', $startDate)
            ->whereRaw('completed_at > created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_minutes')
            ->value('avg_minutes');

        return [
            'active_users' => $activeUsers,
            'avg_responses' => round($averageResponsesPerUser, 1),
            'avg_completion_time' => round($completionTime ?? 0),
        ];
    }

    private function getPopularSurveys()
    {
        return Survey::withCount('responses')
            ->orderByDesc('responses_count')
            ->limit(5)
            ->get()
            ->map(function ($survey) {
                return [
                    'name' => $survey->name,
                    'responses' => $survey->responses_count,
                ];
            });
    }
}
