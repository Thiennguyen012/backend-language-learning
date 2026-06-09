<?php

namespace App\Http\Controllers\Api\Permission;

use App\Http\Controllers\Controller;
use App\CPU\Helpers;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission\Permission;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $perPage = (int) request()->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;

        $permissions = Permission::query()->latest()->paginate($perPage);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.success'),
            'data' => PermissionResource::collection($permissions->getCollection()),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
            ],
        ]);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $permission = Permission::create($data);

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'message' => __('messages.success'),
            'data' => new PermissionResource($permission),
        ], Response::HTTP_CREATED);
    }

    public function show(Permission $permission): JsonResponse
    {
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'data' => new PermissionResource($permission),
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $data = $request->validated();

        $permission->update($data);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.success'),
            'data' => new PermissionResource($permission->fresh()),
        ]);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.success'),
        ]);
    }
}
