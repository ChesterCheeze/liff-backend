<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LineOAUser;
use App\Models\Survey;
use Illuminate\Http\Request;
use App\Models\SurveyResponse;

class SurveyResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validData = $request->validate([
            'lineId' => 'required',
            'survey_id' => 'required',
            'form_data' => 'required',
        ]);

        $lineuser = LineOAUser::where('line_id', $validData['lineId'])->first();
        if (!$lineuser) {
            return response()->json(['message' => 'user' . $validData['lineId'] . 'not found'], 404);
        }
        $lineuser->survey_responses()->create([
            'line_id' => $validData['lineId'],
            'survey_id' => $validData['survey_id'],
            'form_data' => $validData['form_data'],
        ]);

        return response()->json(['message' => 'Data created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
