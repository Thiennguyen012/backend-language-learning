<?php

namespace App\Http\Controllers\Api\UserTestAttempt;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserTestAttempt\StoreUserTestAttemptRequest;
use App\Http\Requests\UserTestAttempt\UpdateUserTestAttemptRequest;
use App\Services\UserTestAttempt\UserTestAttemptService;
use App\Traits\ValidatesRequestData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserTestAttemptController extends Controller
{
    use ValidatesRequestData;

    protected $userTestAttemptService;

    public function __construct(UserTestAttemptService $userTestAttemptService)
    {
        $this->userTestAttemptService = $userTestAttemptService;
    }

    /**
     * Display a listing of user test attempts
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $search = $request->query('search', '');

        $attempts = $this->userTestAttemptService->paginate($perPage, $search);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.user_test_attempt')]),
            'data' => $attempts->items(),
            'meta' => [
                'current_page' => $attempts->currentPage(),
                'last_page' => $attempts->lastPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user test attempt
     */
    public function store(StoreUserTestAttemptRequest $request): JsonResponse
    {
        try {
            $attempt = $this->userTestAttemptService->create($request->validated());

            return response()->json([
                'status_code' => Response::HTTP_CREATED,
                'message' => __('messages.common.created', ['entity' => __('messages.entities.user_test_attempt')]),
                'data' => $attempt,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.create_error', ['entity' => __('messages.entities.user_test_attempt')]));
        }
    }

    /**
     * Display the specified user test attempt
     */
    public function show(string $id): JsonResponse
    {
        $attempt = $this->userTestAttemptService->find($id, ['user', 'collectionTest']);

        if (!$attempt) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.user_test_attempt')]),
            'data' => $attempt,
        ]);
    }

    /**
     * Update the specified user test attempt
     */
    public function update(UpdateUserTestAttemptRequest $request, string $id): JsonResponse
    {
        try {
            $attempt = $this->userTestAttemptService->find($id);

            if (!$attempt) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $updatedAttempt = $this->userTestAttemptService->update($attempt, $request->validated());

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.updated', ['entity' => __('messages.entities.user_test_attempt')]),
                'data' => $updatedAttempt,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.update_error', ['entity' => __('messages.entities.user_test_attempt')]));
        }
    }

    /**
     * Remove the specified user test attempt
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $attempt = $this->userTestAttemptService->find($id);

            if (!$attempt) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->userTestAttemptService->delete($attempt);

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.deleted', ['entity' => __('messages.entities.user_test_attempt')]),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.delete_error', ['entity' => __('messages.entities.user_test_attempt')]));
        }
    }
}
