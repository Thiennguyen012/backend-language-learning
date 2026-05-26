<?php

namespace App\Http\Controllers\Api\Question;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Services\Question\QuestionService;
use App\Traits\ValidatesRequestData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuestionController extends Controller
{
    use ValidatesRequestData;

    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * Display a listing of questions
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $search = $request->query('search', '');

        $questions = $this->questionService->paginate($perPage, $search);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.question')]),
            'data' => $questions->items(),
            'meta' => [
                'current_page' => $questions->currentPage(),
                'last_page' => $questions->lastPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
            ],
        ], Response::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Store a newly created question
     */
    public function store(StoreQuestionRequest $request): JsonResponse
    {
        try {
            $question = $this->questionService->create($request->validated());

            return response()->json([
                'status_code' => Response::HTTP_CREATED,
                'message' => __('messages.common.created', ['entity' => __('messages.entities.question')]),
                'data' => $question,
            ], Response::HTTP_CREATED, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.create_error', ['entity' => __('messages.entities.question')]));
        }
    }

    /**
     * Display the specified question
     */
    public function show(string $id): JsonResponse
    {
        $question = $this->questionService->find($id, ['questionType']);

        if (!$question) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.question')]),
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.question')]),
            'data' => $question,
        ], Response::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the specified question
     */
    public function update(UpdateQuestionRequest $request, string $id): JsonResponse
    {
        try {
            $question = $this->questionService->find($id);

            if (!$question) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.question')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $updatedQuestion = $this->questionService->update($question, $request->validated());

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.updated', ['entity' => __('messages.entities.question')]),
                'data' => $updatedQuestion,
            ], Response::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.update_error', ['entity' => __('messages.entities.question')]));
        }
    }

    /**
     * Remove the specified question
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $question = $this->questionService->find($id);

            if (!$question) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.question')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->questionService->delete($question);

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.deleted', ['entity' => __('messages.entities.question')]),
            ], Response::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.delete_error', ['entity' => __('messages.entities.question')]));
        }
    }
}
