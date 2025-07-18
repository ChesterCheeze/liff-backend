<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exports\SurveyResponsesExport;
use App\Exports\SurveysExport;
use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Survey;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends BaseApiController
{
    public function surveys(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'format' => 'required|in:xlsx,csv',
            'status' => 'nullable|in:draft,active,inactive',
            'section' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $filters = array_filter([
            'status' => $request->status,
            'section' => $request->section,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ]);

        $filename = 'surveys_'.now()->format('Y-m-d_H-i-s').'.'.$request->format;

        try {
            return Excel::download(
                new SurveysExport($filters),
                $filename
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Export failed: '.$e->getMessage(), 500);
        }
    }

    public function surveyResponses(Request $request, Survey $survey)
    {
        $this->requireAdmin();

        $request->validate([
            'format' => 'required|in:xlsx,csv',
        ]);

        $filename = 'survey_'.$survey->id.'_responses_'.now()->format('Y-m-d_H-i-s').'.'.$request->format;

        try {
            return Excel::download(
                new SurveyResponsesExport($survey),
                $filename
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Export failed: '.$e->getMessage(), 500);
        }
    }

    public function allResponses(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'format' => 'required|in:xlsx,csv',
            'survey_ids' => 'nullable|array',
            'survey_ids.*' => 'exists:surveys,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $filename = 'all_survey_responses_'.now()->format('Y-m-d_H-i-s').'.'.$request->format;

        try {
            // For now, we'll use a simple collection export
            // In production, you might want to create a dedicated MultiSurveyResponsesExport
            $responses = collect();

            $surveyIds = $request->survey_ids ?: Survey::pluck('id')->toArray();

            foreach ($surveyIds as $surveyId) {
                $survey = Survey::find($surveyId);
                if ($survey) {
                    $surveyExport = new SurveyResponsesExport($survey);
                    $responses = $responses->concat($surveyExport->collection());
                }
            }

            return response()->json([
                'message' => 'Export completed successfully',
                'total_responses' => $responses->count(),
                'download_url' => url('/api/v1/admin/export/download/'.base64_encode($filename)),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Export failed: '.$e->getMessage(), 500);
        }
    }

    public function analytics(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'format' => 'required|in:xlsx,csv',
            'type' => 'required|in:dashboard,survey_stats,user_activity',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $filename = 'analytics_'.$request->type.'_'.now()->format('Y-m-d_H-i-s').'.'.$request->format;

        try {
            switch ($request->type) {
                case 'dashboard':
                    $data = $this->getDashboardAnalytics($request);
                    break;
                case 'survey_stats':
                    $data = $this->getSurveyStatsAnalytics($request);
                    break;
                case 'user_activity':
                    $data = $this->getUserActivityAnalytics($request);
                    break;
                default:
                    return $this->errorResponse('Invalid analytics type', 400);
            }

            return response()->json([
                'message' => 'Analytics export prepared successfully',
                'data' => $data,
                'download_url' => url('/api/v1/admin/export/analytics/'.$request->type),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Analytics export failed: '.$e->getMessage(), 500);
        }
    }

    private function getDashboardAnalytics(Request $request): array
    {
        $surveys = Survey::with(['responses', 'questions'])->get();

        return $surveys->map(function ($survey) {
            return [
                'survey_id' => $survey->id,
                'name' => $survey->name,
                'status' => $survey->status,
                'total_questions' => $survey->questions->count(),
                'total_responses' => $survey->responses->count(),
                'completed_responses' => $survey->responses()->whereNotNull('completed_at')->count(),
                'response_rate' => $survey->responses->count() > 0
                    ? round(($survey->responses()->whereNotNull('completed_at')->count() / $survey->responses->count()) * 100, 2)
                    : 0,
                'created_at' => $survey->created_at,
            ];
        })->toArray();
    }

    private function getSurveyStatsAnalytics(Request $request): array
    {
        return Survey::withCount(['responses', 'questions'])
            ->with(['responses' => function ($query) {
                $query->whereNotNull('completed_at');
            }])
            ->get()
            ->map(function ($survey) {
                return [
                    'id' => $survey->id,
                    'name' => $survey->name,
                    'section' => $survey->section,
                    'status' => $survey->status,
                    'questions_count' => $survey->questions_count,
                    'total_responses' => $survey->responses_count,
                    'completed_responses' => $survey->responses->count(),
                    'avg_completion_time' => $this->calculateAvgCompletionTime($survey),
                ];
            })
            ->toArray();
    }

    private function getUserActivityAnalytics(Request $request): array
    {
        return \App\Models\LineOAUser::withCount(['responses'])
            ->with(['responses' => function ($query) use ($request) {
                if ($request->date_from) {
                    $query->where('completed_at', '>=', $request->date_from);
                }
                if ($request->date_to) {
                    $query->where('completed_at', '<=', $request->date_to);
                }
            }])
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'line_id' => $user->line_id,
                    'display_name' => $user->display_name,
                    'email' => $user->email,
                    'total_responses' => $user->responses_count,
                    'recent_responses' => $user->responses->count(),
                    'last_activity' => $user->responses->max('completed_at'),
                ];
            })
            ->toArray();
    }

    private function calculateAvgCompletionTime(Survey $survey): ?float
    {
        $responses = $survey->responses()
            ->whereNotNull('completed_at')
            ->get();

        if ($responses->isEmpty()) {
            return null;
        }

        $totalTime = $responses->sum(function ($response) {
            return $response->completed_at->diffInMinutes($response->created_at);
        });

        return round($totalTime / $responses->count(), 2);
    }
}
