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
        $survey = Survey::find($survey_id);
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
        $question = new SurveyQuestion($validateData);
        $question->save();
        return with('success', 'Question created successfully.');
    }
}
