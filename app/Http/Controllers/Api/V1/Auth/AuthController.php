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
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends BaseApiController
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/admin/login",
     *     summary="Admin login",
     *     description="Authenticate admin user and receive JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Admin login successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        // Send email verification
        $user->sendEmailVerificationNotification();

        return $this->successResponse([
            'user' => new UserResource($user),
            'message' => 'Registration successful. Please check your email to verify your account.',
        ], 'User registered successfully', 201);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->errorResponse('User not found', 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email already verified');
        }

        if (! $user->verifyEmailToken($request->token)) {
            return $this->errorResponse('Invalid verification token', 400);
        }

        $user->markEmailAsVerified();

        return $this->successResponse(null, 'Email verified successfully');
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified', 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->successResponse(null, 'Verification email sent');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(null, 'Password reset link sent to your email');
        }

        return $this->errorResponse('Unable to send password reset link', 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all tokens
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, 'Password reset successful');
        }

        return $this->errorResponse('Password reset failed', 400);
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

        if ($user instanceof \App\Models\User) {
            return $this->successResponse(new UserResource($user), 'User retrieved successfully');
        } elseif ($user instanceof \App\Models\LineOAUser) {
            return $this->successResponse(new LineOAUserResource($user), 'User retrieved successfully');
        } else {
            return $this->errorResponse('User type not supported', 400);
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
