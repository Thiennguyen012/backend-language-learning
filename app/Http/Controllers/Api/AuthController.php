<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Services\User\UserAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(UserAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $result = $this->authService->login($credentials);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.success', ['entity' => __('messages.entities.login')]),
            'data' => [
                'user' => $result['user'],
                'token' => $result['token'],
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.success', ['entity' => __('messages.entities.logout')]),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('roles.permissions');
        $permissions = $user->roles
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->unique('id')
            ->values();
        $payload = $user->toArray();
        unset($payload['roles']);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.profile')]),
            'data' => [
                ...$payload,
                'permissions' => $permissions,
            ]
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $data = $request->validated();

        $updatedUser = $this->authService->update($request->user(), $data);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.updated', ['entity' => __('messages.entities.profile')]),
            'data' => $updatedUser
        ]);
    }
}
