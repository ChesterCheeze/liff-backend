<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $surveyQuestions = SurveyQuestion::all();
        return response()->json($surveyQuestions);
    }

    /**
     * 
     */
    public function create()
    {
        //
        $surveys = Survey::all();
        return view('survey.create', ['surveys' => $surveys]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validateData = $request->validate([
            'section' => 'required',
            'name' => 'required',
            'description' => 'required',]);
        Survey::create($validateData);
        return redirect()->route('survey.create')->with('success', 'Survey created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
        return view('survey.create');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
    *
    */
    public function showSurveyTable ()
    {
        $surveyQuestions = SurveyQuestion::all();
        return view('survey.survey', ['surveyQuestions' => $surveyQuestions]);
    }

    public function edit(string $id)
    {
        $survey = Survey::find($id);
        return view('survey.edit', ['survey' => $survey]);
    }
}
