<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get total users
        $totalUsers = LineOAUser::count();
        $newUsers = LineOAUser::where('created_at', '>=', now()->subDays(30))
            ->count();
        $userGrowth = $this->calculateGrowth($totalUsers, $newUsers);

        // Get total surveys
        $totalSurveys = Survey::count();
        $newSurveys = Survey::where('created_at', '>=', now()->subDays(30))->count();
        $surveyGrowth = $this->calculateGrowth($totalSurveys, $newSurveys);

        // Get total responses
        $totalResponses = SurveyResponse::count();
        $newResponses = SurveyResponse::where('created_at', '>=', now()->subDays(30))->count();
        $responseGrowth = $this->calculateGrowth($totalResponses, $newResponses);

        // Get recent activity
        $recentActivity = $this->getRecentActivity();

        return view('admin.dashboard', compact(
            'totalUsers',
            'userGrowth',
            'totalSurveys',
            'surveyGrowth',
            'totalResponses',
            'responseGrowth',
            'recentActivity'
        ));
    }

    private function calculateGrowth($total, $new)
    {
        if ($total - $new <= 0) {
            return 100;
        }

        return round(($new / ($total - $new)) * 100);
    }

    private function getRecentActivity()
    {
        return DB::table('survey_responses')
            ->join('lineoausers', 'survey_responses.line_id', '=', 'lineoausers.line_id')
            ->join('surveys', 'survey_responses.survey_id', '=', 'surveys.id')
            ->select('lineoausers.name as user_name', 'surveys.name', 'survey_responses.created_at')
            ->orderBy('survey_responses.created_at', 'desc')
            ->limit(5)
            ->get();
    }
}
