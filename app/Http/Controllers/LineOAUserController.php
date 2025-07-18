<?php

namespace App\Http\Controllers;

use App\Models\LineOAUser;
use Illuminate\Http\Request;

class LineOAUserController extends Controller
{
    //
    public function store(Request $request)
    {
        //
        $validateData = $request->validate([
            'lineId' => 'required',
            'name' => 'required',
            'pictureUrl' => 'required', ]);

        $lineuser = LineOAUser::where('line_id', $validateData['lineId'])->first();
        if (! $lineuser) {
            $lineuser = LineOAUser::create([
                'line_id' => $validateData['lineId'],
                'name' => $validateData['name'],
                'picture_url' => $validateData['pictureUrl'],
            ]);
            $token = $lineuser->createToken('authToken');

            return response()->json(['message' => 'User registered successfully', 'api_token' => $token->plainTextToken]);
        }

        return response()->json(['message' => 'User already registered']);
    }
}
