<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Resources\V1\LineOAUserResource;
use App\Http\Resources\V1\UserResource;
use App\Models\LineOAUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    public function adminLogin(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        if (! $user->isAdmin()) {
            return $this->errorResponse('Admin access required', 403);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Admin login successful');
    }

    public function lineAuth(Request $request)
    {
        $request->validate([
            'line_id' => 'required|string',
            'name' => 'required|string',
            'picture_url' => 'nullable|url',
        ]);

        $lineUser = LineOAUser::where('line_id', $request->line_id)->first();

        if (! $lineUser) {
            $lineUser = LineOAUser::create([
                'line_id' => $request->line_id,
                'name' => $request->name,
                'picture_url' => $request->picture_url,
            ]);
        } else {
            $lineUser->update([
                'name' => $request->name,
                'picture_url' => $request->picture_url,
            ]);
        }

        $token = $lineUser->createToken('line-token')->plainTextToken;

        return $this->successResponse([
            'user' => new LineOAUserResource($lineUser),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'LINE authentication successful');
    }

    public function user(Request $request)
    {
        $user = $request->user();

        if ($user instanceof User) {
            return $this->successResponse(new UserResource($user), 'User retrieved successfully');
        } else {
            return $this->successResponse(new LineOAUserResource($user), 'User retrieved successfully');
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();

        // Delete current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('refresh-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Token refreshed successfully');
    }
}
