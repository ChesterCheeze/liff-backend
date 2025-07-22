<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\SurveyResponseCreated;
use App\Http\Requests\V1\SurveyResponseRequest;
use App\Http\Resources\V1\SurveyResponseResource;
use App\Models\Survey;
use App\Models\SurveyResponse;

class SurveyResponseController extends BaseApiController
{
    /**
     * @OA\Post(
     *     path="/api/v1/survey-responses",
     *     summary="Submit survey response",
     *     description="Submit a response to a survey",
     *     tags={"Survey Responses"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SurveyResponseRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Response submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Survey response submitted successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SurveyResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Survey not found or not active",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Response already submitted",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Rate limit exceeded",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(SurveyResponseRequest $request)
    {
        try {
            $user = $this->getCurrentUser();

            if (! $user) {
                return $this->errorResponse('Unauthenticated', 401);
            }

            // Get validated and sanitized data
            $validatedData = $request->validated();

            // Check if survey exists and is active
            $survey = Survey::where('id', $validatedData['survey_id'])
                ->where('status', 'active')
                ->first();

            if (! $survey) {
                return $this->errorResponse('Survey not found or not active', 404);
            }

            // Check if user already submitted response for this survey
            $existingResponse = SurveyResponse::where('survey_id', $validatedData['survey_id'])
                ->where('user_id', $user->id)
                ->where('user_type', get_class($user))
                ->first();

            if ($existingResponse) {
                return $this->errorResponse('Response already submitted for this survey', 409);
            }

            $responseData = [
                'survey_id' => $validatedData['survey_id'],
                'form_data' => $validatedData['answers'], // This is now sanitized
                'completed_at' => now(),
                'user_id' => $user->id,
                'user_type' => get_class($user),
            ];

            // For backward compatibility with LINE users
            if ($user instanceof \App\Models\LineOAUser) {
                $responseData['line_id'] = $user->line_id;
            }

            $response = SurveyResponse::create($responseData);
            $response->load('survey');

            // event(new SurveyResponseCreated($response));

            return $this->successResponse(new SurveyResponseResource($response), 'Survey response submitted successfully', 201);

        } catch (\Exception $e) {
            \Log::error('Error in SurveyResponseController store', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->errorResponse('Internal server error: '.$e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/survey-responses/{surveyResponse}",
     *     summary="Get survey response",
     *     description="Retrieve specific survey response (only for the user who submitted it)",
     *     tags={"Survey Responses"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="surveyResponse",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Survey Response ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Survey response retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Survey response retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SurveyResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied to this response",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Survey response not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Rate limit exceeded",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(SurveyResponse $surveyResponse)
    {
        $user = $this->getCurrentUser();

        // Check if the response belongs to the current user
        $belongsToUser = ($surveyResponse->user_id === $user->id && $surveyResponse->user_type === get_class($user));

        // For backward compatibility with LINE users
        if (! $belongsToUser && $user instanceof \App\Models\LineOAUser) {
            $belongsToUser = ($surveyResponse->line_id === $user->line_id);
        }

        if (! $belongsToUser) {
            return $this->forbiddenResponse('Access denied to this response');
        }

        return $this->successResponse(new SurveyResponseResource($surveyResponse), 'Survey response retrieved successfully');
    }
}
