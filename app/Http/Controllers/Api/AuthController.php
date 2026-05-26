<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'message' => 'Login successful',
            'data' => [
                'user' => $result['user'],
                'token' => $result['token'],
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'Logout successful'
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
            'message' => 'Get profile success',
            'data' => [
                ...$payload,
                'permissions' => $permissions,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'birthday' => 'sometimes|date',
            'address' => 'sometimes|string|max:256',
            'avatar' => 'sometimes|string',
            'password' => 'sometimes|string|min:6',
        ]);

        $updatedUser = $this->authService->update($request->user(), $data);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'Profile updated successfully',
            'data' => $updatedUser
        ]);
    }
}
