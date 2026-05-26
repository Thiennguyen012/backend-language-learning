<?php

namespace App\Http\Controllers\Api\CollectionTest;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\CollectionTest\StoreCollectionTestRequest;
use App\Http\Requests\CollectionTest\UpdateCollectionTestRequest;
use App\Services\CollectionTest\CollectionTestService;
use App\Traits\ValidatesRequestData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CollectionTestController extends Controller
{
    use ValidatesRequestData;

    protected $collectionTestService;

    public function __construct(CollectionTestService $collectionTestService)
    {
        $this->collectionTestService = $collectionTestService;
    }

    /**
     * Display a listing of collection tests
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $search = $request->query('search', '');

        $collectionTests = $this->collectionTestService->paginate($perPage, $search);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.collection_test')]),
            'data' => $collectionTests->items(),
            'meta' => [
                'current_page' => $collectionTests->currentPage(),
                'last_page' => $collectionTests->lastPage(),
                'per_page' => $collectionTests->perPage(),
                'total' => $collectionTests->total(),
            ],
        ]);
    }

    /**
     * Store a newly created collection test
     */
    public function store(StoreCollectionTestRequest $request): JsonResponse
    {
        try {
            $collectionTest = $this->collectionTestService->create($request->validated());

            return response()->json([
                'status_code' => Response::HTTP_CREATED,
                'message' => __('messages.common.created', ['entity' => __('messages.entities.collection_test')]),
                'data' => $collectionTest,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.create_error', ['entity' => __('messages.entities.collection_test')]));
        }
    }

    /**
     * Display the specified collection test
     */
    public function show(string $id): JsonResponse
    {
        $collectionTest = $this->collectionTestService->find($id, ['questions']);

        if (!$collectionTest) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.collection_test')]),
                Response::HTTP_NOT_FOUND
            );
        }

        $collectionTest->setAppends([]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.collection_test')]),
            'data' => $collectionTest,
        ]);
    }

    /**
     * Update the specified collection test
     */
    public function update(UpdateCollectionTestRequest $request, string $id): JsonResponse
    {
        try {
            $collectionTest = $this->collectionTestService->find($id);

            if (!$collectionTest) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.collection_test')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $updatedCollectionTest = $this->collectionTestService->update($collectionTest, $request->validated());

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.updated', ['entity' => __('messages.entities.collection_test')]),
                'data' => $updatedCollectionTest,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.update_error', ['entity' => __('messages.entities.collection_test')]));
        }
    }

    /**
     * Remove the specified collection test
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $collectionTest = $this->collectionTestService->find($id);

            if (!$collectionTest) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.collection_test')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->collectionTestService->delete($collectionTest);

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.deleted', ['entity' => __('messages.entities.collection_test')]),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.delete_error', ['entity' => __('messages.entities.collection_test')]));
        }
    }
}
