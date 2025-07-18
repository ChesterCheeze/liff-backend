<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\SurveyResponseRequest;
use App\Http\Resources\V1\SurveyResponseResource;
use App\Models\Survey;
use App\Models\SurveyResponse;

class SurveyResponseController extends BaseApiController
{
    public function store(SurveyResponseRequest $request)
    {
        $user = $this->getCurrentUser();

        // Check if survey exists and is active
        $survey = Survey::where('id', $request->survey_id)
            ->where('status', 'active')
            ->first();

        if (! $survey) {
            return $this->errorResponse('Survey not found or not active', 404);
        }

        // Check if user already submitted response for this survey
        $existingResponse = SurveyResponse::where('survey_id', $request->survey_id)
            ->where('line_id', $user->line_id)
            ->first();

        if ($existingResponse) {
            return $this->errorResponse('Response already submitted for this survey', 409);
        }

        $response = SurveyResponse::create([
            'line_id' => $user->line_id,
            'survey_id' => $request->survey_id,
            'answers' => $request->answers,
            'completed_at' => now(),
        ]);

        return $this->successResponse(new SurveyResponseResource($response), 'Survey response submitted successfully', 201);
    }

    public function show(SurveyResponse $surveyResponse)
    {
        $user = $this->getCurrentUser();

        // Check if the response belongs to the current user
        if ($surveyResponse->line_id !== $user->line_id) {
            return $this->forbiddenResponse('Access denied to this response');
        }

        return $this->successResponse(new SurveyResponseResource($surveyResponse), 'Survey response retrieved successfully');
    }
}
