<?php

namespace App\Http\Controllers\Api\UserTestAnswer;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserTestAnswer\StoreUserTestAnswerRequest;
use App\Http\Requests\UserTestAnswer\UpdateUserTestAnswerRequest;
use App\Services\UserTestAnswer\UserTestAnswerService;
use App\Traits\ValidatesRequestData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserTestAnswerController extends Controller
{
    use ValidatesRequestData;

    protected $userTestAnswerService;

    public function __construct(UserTestAnswerService $userTestAnswerService)
    {
        $this->userTestAnswerService = $userTestAnswerService;
    }

    /**
     * Display a listing of user test answers
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $search = $request->query('search', '');

        $answers = $this->userTestAnswerService->paginate($perPage, $search);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.user_test_answer')]),
            'data' => $answers->items(),
            'meta' => [
                'current_page' => $answers->currentPage(),
                'last_page' => $answers->lastPage(),
                'per_page' => $answers->perPage(),
                'total' => $answers->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user test answer
     */
    public function store(StoreUserTestAnswerRequest $request): JsonResponse
    {
        try {
            $payload = $request->validated();
            $existing = $this->userTestAnswerService->findByAttemptAndQuestion(
                $payload['user_test_attempt_id'],
                $payload['question_id']
            );

            $answer = $existing
                ? $this->userTestAnswerService->update($existing, $payload)
                : $this->userTestAnswerService->create($payload);

            return response()->json([
                'status_code' => $existing ? Response::HTTP_OK : Response::HTTP_CREATED,
                'message' => $existing
                    ? __('messages.common.updated', ['entity' => __('messages.entities.user_test_answer')])
                    : __('messages.common.created', ['entity' => __('messages.entities.user_test_answer')]),
                'data' => $answer,
            ], $existing ? Response::HTTP_OK : Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.create_error', ['entity' => __('messages.entities.user_test_answer')]));
        }
    }

    /**
     * Display the specified user test answer
     */
    public function show(string $id): JsonResponse
    {
        $answer = $this->userTestAnswerService->find($id, ['attempt', 'question']);

        if (!$answer) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.user_test_answer')]),
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.user_test_answer')]),
            'data' => $answer,
        ]);
    }

    /**
     * Update the specified user test answer
     */
    public function update(UpdateUserTestAnswerRequest $request, string $id): JsonResponse
    {
        try {
            $answer = $this->userTestAnswerService->find($id);

            if (!$answer) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.user_test_answer')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $updatedAnswer = $this->userTestAnswerService->update($answer, $request->validated());

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.updated', ['entity' => __('messages.entities.user_test_answer')]),
                'data' => $updatedAnswer,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.update_error', ['entity' => __('messages.entities.user_test_answer')]));
        }
    }

    /**
     * Remove the specified user test answer
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $answer = $this->userTestAnswerService->find($id);

            if (!$answer) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.user_test_answer')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->userTestAnswerService->delete($answer);

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.deleted', ['entity' => __('messages.entities.user_test_answer')]),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.delete_error', ['entity' => __('messages.entities.user_test_answer')]));
        }
    }
}
