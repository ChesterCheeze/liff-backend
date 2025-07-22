<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\SurveyCollection;
use App\Http\Resources\V1\SurveyResource;
use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/surveys",
     *     summary="List active surveys",
     *     description="Get list of active surveys available to public",
     *     tags={"Surveys"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer", default=1),
     *         description="Page number for pagination"
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer", default=15, maximum=100),
     *         description="Number of items per page"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         @OA\Schema(type="string", enum={"draft", "active", "closed"}),
     *         description="Filter by survey status"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Surveys retrieved successfully",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/Survey")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Rate limit exceeded",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $this->getPerPage();
        $surveys = Survey::with('questions')
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->paginate($perPage);

        return new SurveyCollection($surveys);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/surveys/{survey}",
     *     summary="Get survey details",
     *     description="Retrieve specific survey with questions",
     *     tags={"Surveys"},
     *     @OA\Parameter(
     *         name="survey",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Survey ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Survey details retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Survey retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Survey")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Survey not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Rate limit exceeded",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(Survey $survey)
    {
        $survey->load('questions');

        return $this->successResponse(new SurveyResource($survey), 'Survey retrieved successfully');
    }
}
