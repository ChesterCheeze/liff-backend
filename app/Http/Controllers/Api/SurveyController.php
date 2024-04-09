<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\Models\Survey;
use Illuminate\Http\Request;
use App\Models\LineOAUser;

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
    public function show(Request $request, $id)
    {
        $lineId = $request->input('lineId');
        $name = $request->input('name');
        $pictureUrl = $request->input('pictureUrl');

        $lineUser = $this->getOrCreateUser($lineId, $name, $pictureUrl);
        $survey = $this->getSurvey($id);

        $token = $lineUser->createToken('authToken');

        return response()->json([
            'apiToken' => $token->plainTextToken,
            'survey' => $survey,
        ]);
       
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $survey = Survey::find($id);
        $validateData = $request->validate([
            'section' => 'required',
            'name' => 'required',
            'description' => 'required',
        ]);
        $survey->update($validateData);
        return redirect()->route('survey.create')->with('success', 'Survey updated successfully.');
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

    private function getOrCreateUser($lineId, $name, $pictureUrl)
    {
        $lineUser = LineOAUser::where('line_id', $lineId)->first();

        if ($lineUser == null) {
            $lineUser = LineOAUser::create([
                'line_id' => $lineId,
                'name' => $name,
                'picture_url' => $pictureUrl,
            ]);
        }

        return $lineUser;
    }

    private function getSurvey($surveyId)
    {
        $survey = Survey::with('questions')->find($surveyId);

        if (!$survey) {
            return response()->json(['message' => 'Survey not found.'], 404);
        }

        $survey->makeHidden(['created_at', 'updated_at']);
        foreach ($survey->questions as $question) {
            $question->makeHidden(['created_at', 'updated_at']);
        }

        return $survey;
    }

}
