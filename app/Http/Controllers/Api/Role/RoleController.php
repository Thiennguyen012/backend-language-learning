<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use App\CPU\Helpers;
use App\Http\Resources\RoleResource;
use App\Models\Role\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $perPage = (int) request()->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;

        $roles = Role::query()->with('permissions')->latest()->paginate($perPage);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.success'),
            'data' => RoleResource::collection($roles->getCollection()),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'role_name' => ['required', 'string', 'max:255'],
            'descriptions' => ['nullable', 'string'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'role_name' => $data['role_name'],
            'descriptions' => $data['descriptions'] ?? null,
        ]);

        if (!empty($data['permission_ids'])) {
            $role->permissions()->sync($data['permission_ids']);
        }

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'message' => __('messages.role_created'),
            'data' => new RoleResource($role->load('permissions')),
        ], Response::HTTP_CREATED);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'data' => new RoleResource($role->load('permissions')),
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'role_name' => ['sometimes', 'required', 'string', 'max:255'],
            'descriptions' => ['nullable', 'string'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update([
            'role_name' => $data['role_name'] ?? $role->role_name,
            'descriptions' => array_key_exists('descriptions', $data)
                ? $data['descriptions']
                : $role->descriptions,
        ]);

        if (array_key_exists('permission_ids', $data)) {
            $role->permissions()->sync($data['permission_ids'] ?? []);
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.role_updated'),
            'data' => new RoleResource($role->load('permissions')),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.role_deleted'),
        ]);
    }
}
