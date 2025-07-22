<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\SurveyStatusChanged;
use App\Events\SurveyUpdated;
use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\V1\SurveyRequest;
use App\Http\Resources\V1\SurveyCollection;
use App\Http\Resources\V1\SurveyResource;
use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/surveys",
     *     summary="List all surveys (Admin)",
     *     description="Get paginated list of all surveys with filtering options",
     *     tags={"Admin - Surveys"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by survey status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "active", "closed"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Surveys retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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

        event(new SurveyUpdated($survey, 'created'));

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

        event(new SurveyUpdated($survey, 'updated'));

        return $this->successResponse(new SurveyResource($survey), 'Survey updated successfully');
    }

    public function destroy(Survey $survey)
    {
        $this->requireAdmin();

        // Check if survey has responses
        if ($survey->responses()->count() > 0) {
            return $this->errorResponse('Cannot delete survey with existing responses', 409);
        }

        event(new SurveyUpdated($survey, 'deleted'));

        $survey->delete();

        return $this->successResponse(null, 'Survey deleted successfully');
    }

    public function updateStatus(Request $request, Survey $survey)
    {
        $this->requireAdmin();

        $request->validate([
            'status' => 'required|in:draft,active,inactive',
        ]);

        $oldStatus = $survey->status;
        $survey->update(['status' => $request->status]);

        event(new SurveyStatusChanged($survey, $oldStatus, $request->status));

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
