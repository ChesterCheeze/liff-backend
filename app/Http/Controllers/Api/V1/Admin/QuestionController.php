<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\V1\SurveyQuestionRequest;
use App\Http\Resources\V1\SurveyQuestionResource;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;

class QuestionController extends BaseApiController
{
    public function index(Request $request, Survey $survey)
    {
        $this->requireAdmin();

        $questions = $survey->questions()->orderBy('id')->get();

        return $this->successResponse(
            SurveyQuestionResource::collection($questions),
            'Questions retrieved successfully'
        );
    }

    public function store(SurveyQuestionRequest $request, Survey $survey)
    {
        $this->requireAdmin();

        $validatedData = $request->validated();
        $validatedData['survey_id'] = $survey->id;

        $question = SurveyQuestion::create($validatedData);

        return $this->successResponse(new SurveyQuestionResource($question), 'Question created successfully', 201);
    }

    public function show(SurveyQuestion $question)
    {
        $this->requireAdmin();

        return $this->successResponse(new SurveyQuestionResource($question), 'Question retrieved successfully');
    }

    public function update(SurveyQuestionRequest $request, SurveyQuestion $question)
    {
        $this->requireAdmin();

        $question->update($request->validated());

        return $this->successResponse(new SurveyQuestionResource($question), 'Question updated successfully');
    }

    public function destroy(SurveyQuestion $question)
    {
        $this->requireAdmin();

        $question->delete();

        return $this->successResponse(null, 'Question deleted successfully');
    }
}
