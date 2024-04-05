<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SurveyQuestion;
use App\Models\Survey;

class SurveyQuestionController extends Controller
{
    //
    public function index($survey_id)
    {
        $survey = Survey::with('questions')->find($survey_id);
        return view('survey.questions', ['survey' => $survey]);
    }

    public function store(Request $request)
    {
        $validateData = $request->validate([
            'label' => 'required',
            'name' => 'required',
            'type' => 'required',
            'required' => 'required',
            'survey_id' => 'required',
        ]);
        $validateData['required'] = $validateData['required'] == 'true' ? 1 : 0;

        $survey = Survey::find($request->survey_id);
        $survey->questions()->create($validateData);
        return redirect()->route('survey.questions', ['id' => $survey->id])->with('success', 'Question created successfully.');
    }
}
