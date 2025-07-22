<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\V1\Admin\UserRequest;
use App\Http\Resources\V1\LineOAUserResource;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Models\LineOAUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     summary="List all users (Admin)",
     *     description="Get paginated list of all registered users",
     *     tags={"Admin - Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by user role",
     *         required=false,
     *         @OA\Schema(type="string", enum={"admin", "user"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $this->requireAdmin();

        $perPage = $this->getPerPage();
        $users = User::when($request->role, function ($query, $role) {
            return $query->where('role', $role);
        })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return new UserCollection($users);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users/{user}",
     *     summary="Get user details (Admin)",
     *     description="Retrieve specific user details",
     *     tags={"Admin - Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function show(User $user)
    {
        $this->requireAdmin();

        return $this->successResponse(new UserResource($user), 'User retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/users/{user}",
     *     summary="Update user",
     *     description="Update user information",
     *     tags={"Admin - Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UserRequest $request, User $user)
    {
        $this->requireAdmin();

        $validatedData = $request->validated();

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        return $this->successResponse(new UserResource($user), 'User updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/users/{user}",
     *     summary="Delete user",
     *     description="Delete a user account",
     *     tags={"Admin - Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=409, description="Cannot delete own account")
     * )
     */
    public function destroy(User $user)
    {
        $this->requireAdmin();

        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return $this->errorResponse('Cannot delete your own account', 409);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/line-users",
     *     summary="List LINE users (Admin)",
     *     description="Get paginated list of all LINE OA users",
     *     tags={"Admin - Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by user role",
     *         required=false,
     *         @OA\Schema(type="string", enum={"admin", "user"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="LINE users retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function lineUsers(Request $request)
    {
        $this->requireAdmin();

        $perPage = $this->getPerPage();
        $lineUsers = LineOAUser::with(['survey_responses' => function ($query) {
            $query->latest()->take(5);
        }])
            ->when($request->role, function ($query, $role) {
                return $query->where('role', $role);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->paginatedResponse(
            $lineUsers->setCollection(
                $lineUsers->getCollection()->map(fn ($user) => new LineOAUserResource($user))
            ),
            'LINE users retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/line-users/{lineUser}",
     *     summary="Get LINE user details (Admin)",
     *     description="Retrieve specific LINE user details",
     *     tags={"Admin - Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="lineUser",
     *         in="path",
     *         description="LINE User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="LINE user details retrieved",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="LINE user not found")
     * )
     */
    public function showLineUser(LineOAUser $lineUser)
    {
        $this->requireAdmin();

        $lineUser->load('survey_responses.survey');

        return $this->successResponse(new LineOAUserResource($lineUser), 'LINE user retrieved successfully');
    }
}
