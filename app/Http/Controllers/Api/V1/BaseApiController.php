<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BaseApiController extends Controller
{
    use ApiResponseTrait, AuthorizesRequests, ValidatesRequests;

    protected int $defaultPerPage = 15;

    protected int $maxPerPage = 100;

    protected function getPerPage(): int
    {
        $perPage = request()->input('per_page', $this->defaultPerPage);

        return min((int) $perPage, $this->maxPerPage);
    }

    protected function getCurrentUser()
    {
        return auth()->user();
    }

    protected function requireAdmin()
    {
        $user = $this->getCurrentUser();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required');
        }

        return $user;
    }
}
