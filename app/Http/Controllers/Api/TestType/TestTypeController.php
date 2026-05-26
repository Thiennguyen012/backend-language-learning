<?php

namespace App\Http\Controllers\Api\TestType;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\TestType\StoreTestTypeRequest;
use App\Http\Requests\TestType\UpdateTestTypeRequest;
use App\Services\TestType\TestTypeService;
use App\Traits\ValidatesRequestData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TestTypeController extends Controller
{
    use ValidatesRequestData;

    protected $testTypeService;

    public function __construct(TestTypeService $testTypeService)
    {
        $this->testTypeService = $testTypeService;
    }

    /**
     * Display a listing of test types
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $search = $request->query('search', '');

        $testTypes = $this->testTypeService->paginate($perPage, $search);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.test_type')]),
            'data' => $testTypes->items(),
            'meta' => [
                'current_page' => $testTypes->currentPage(),
                'last_page' => $testTypes->lastPage(),
                'per_page' => $testTypes->perPage(),
                'total' => $testTypes->total(),
            ],
        ]);
    }

    /**
     * Store a newly created test type
     */
    public function store(StoreTestTypeRequest $request): JsonResponse
    {
        try {
            $testType = $this->testTypeService->create($request->validated());

            return response()->json([
                'status_code' => Response::HTTP_CREATED,
                'message' => __('messages.common.created', ['entity' => __('messages.entities.test_type')]),
                'data' => $testType,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.create_error', ['entity' => __('messages.entities.test_type')]));
        }
    }

    /**
     * Display the specified test type
     */
    public function show(string $id): JsonResponse
    {
        $testType = $this->testTypeService->find($id);

        if (!$testType) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.test_type')]),
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.test_type')]),
            'data' => $testType,
        ]);
    }

    /**
     * Update the specified test type
     */
    public function update(UpdateTestTypeRequest $request, string $id): JsonResponse
    {
        try {
            $testType = $this->testTypeService->find($id);

            if (!$testType) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.test_type')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $updatedTestType = $this->testTypeService->update($testType, $request->validated());

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.updated', ['entity' => __('messages.entities.test_type')]),
                'data' => $updatedTestType,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.update_error', ['entity' => __('messages.entities.test_type')]));
        }
    }

    /**
     * Remove the specified test type
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $testType = $this->testTypeService->find($id);

            if (!$testType) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.test_type')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->testTypeService->delete($testType);

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.deleted', ['entity' => __('messages.entities.test_type')]),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.delete_error', ['entity' => __('messages.entities.test_type')]));
        }
    }
}
