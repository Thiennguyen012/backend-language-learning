<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use App\CPU\Helpers;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role\Role;
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
            'message' => __('messages.common.list', ['entity' => __('messages.entities.role')]),
            'data' => RoleResource::collection($roles->getCollection()),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $role = Role::create([
            'role_name' => $data['role_name'],
            'descriptions' => $data['descriptions'] ?? null,
        ]);

        if (!empty($data['permission_ids'])) {
            $role->permissions()->sync($data['permission_ids']);
        }

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'message' => __('messages.common.created', ['entity' => __('messages.entities.role')]),
            'data' => new RoleResource($role->load('permissions')),
        ], Response::HTTP_CREATED);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.role')]),
            'data' => new RoleResource($role->load('permissions')),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $data = $request->validated();

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
            'message' => __('messages.common.updated', ['entity' => __('messages.entities.role')]),
            'data' => new RoleResource($role->load('permissions')),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.deleted', ['entity' => __('messages.entities.role')]),
        ]);
    }
}
