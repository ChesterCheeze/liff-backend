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

    public function show(User $user)
    {
        $this->requireAdmin();

        return $this->successResponse(new UserResource($user), 'User retrieved successfully');
    }

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

    public function showLineUser(LineOAUser $lineUser)
    {
        $this->requireAdmin();

        $lineUser->load('survey_responses.survey');

        return $this->successResponse(new LineOAUserResource($lineUser), 'LINE user retrieved successfully');
    }
}
