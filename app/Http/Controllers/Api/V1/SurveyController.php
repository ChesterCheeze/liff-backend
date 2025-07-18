<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\SurveyCollection;
use App\Http\Resources\V1\SurveyResource;
use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends BaseApiController
{
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

    public function show(Survey $survey)
    {
        $survey->load('questions');

        return $this->successResponse(new SurveyResource($survey), 'Survey retrieved successfully');
    }
}
