<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;

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
        $validateData['required'] = $validateData['required'] == 'checked' ? 1 : 0;

        $survey = Survey::find($request->survey_id);
        $survey->questions()->create($validateData);

        return redirect()->route('survey.questions', ['id' => $survey->id])->with('success', 'Question created successfully.');
    }

    public function edit($id)
    {
        $question = SurveyQuestion::find($id);

        return view('survey.edit-question', ['question' => $question]);
    }

    public function update(Request $request, $id)
    {
        $validateData = $request->validate([
            'label' => 'required',
            'name' => 'required',
            'type' => 'required',
            'required' => 'required',
            'survey_id' => 'required',
        ]);
        $validateData['required'] = $validateData['required'] == 'checked' ? 1 : 0;

        $question = SurveyQuestion::find($id);
        $question->update($validateData);

        return redirect()->route('survey.questions', ['id' => $question->survey_id])->with('success', 'Question updated successfully.');
    }

    public function destroy($id)
    {
        $question = SurveyQuestion::find($id);
        $survey_id = $question->survey_id;
        $question->delete();

        return redirect()->route('survey.questions', ['id' => $survey_id])->with('success', 'Question deleted successfully.');
    }
}
