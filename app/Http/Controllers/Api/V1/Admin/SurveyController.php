<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\V1\SurveyRequest;
use App\Http\Resources\V1\SurveyCollection;
use App\Http\Resources\V1\SurveyResource;
use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends BaseApiController
{
    public function index(Request $request)
    {
        $this->requireAdmin();

        $perPage = $this->getPerPage();
        $surveys = Survey::with(['questions', 'responses'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->section, function ($query, $section) {
                return $query->where('section', $section);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return new SurveyCollection($surveys);
    }

    public function store(SurveyRequest $request)
    {
        $this->requireAdmin();

        $survey = Survey::create($request->validated());

        return $this->successResponse(new SurveyResource($survey), 'Survey created successfully', 201);
    }

    public function show(Survey $survey)
    {
        $this->requireAdmin();

        $survey->load(['questions', 'responses.lineOaUser']);

        return $this->successResponse(new SurveyResource($survey), 'Survey retrieved successfully');
    }

    public function update(SurveyRequest $request, Survey $survey)
    {
        $this->requireAdmin();

        $survey->update($request->validated());

        return $this->successResponse(new SurveyResource($survey), 'Survey updated successfully');
    }

    public function destroy(Survey $survey)
    {
        $this->requireAdmin();

        // Check if survey has responses
        if ($survey->responses()->count() > 0) {
            return $this->errorResponse('Cannot delete survey with existing responses', 409);
        }

        $survey->delete();

        return $this->successResponse(null, 'Survey deleted successfully');
    }

    public function updateStatus(Request $request, Survey $survey)
    {
        $this->requireAdmin();

        $request->validate([
            'status' => 'required|in:draft,active,inactive',
        ]);

        $survey->update(['status' => $request->status]);

        return $this->successResponse([
            'id' => $survey->id,
            'status' => $survey->status,
        ], 'Survey status updated successfully');
    }

    public function analytics(Survey $survey)
    {
        $this->requireAdmin();

        $totalResponses = $survey->responses()->count();
        $recentResponses = $survey->responses()
            ->where('completed_at', '>=', now()->subDays(7))
            ->count();

        $responsesByDay = $survey->responses()
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
            ->where('completed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $this->successResponse([
            'survey_id' => $survey->id,
            'total_responses' => $totalResponses,
            'recent_responses' => $recentResponses,
            'responses_by_day' => $responsesByDay,
            'total_questions' => $survey->questions()->count(),
        ], 'Survey analytics retrieved successfully');
    }
}
