<?php

namespace App\Http\Controllers\Api\Flashcard;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Flashcard\StoreFlashcardRequest;
use App\Http\Requests\Flashcard\UpdateFlashcardRequest;
use App\Services\Flashcard\FlashcardService;
use App\Traits\ValidatesRequestData;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    use ValidatesRequestData;

    protected $flashcardService;

    public function __construct(FlashcardService $flashcardService)
    {
        $this->flashcardService = $flashcardService;
    }

    /**
     * Display a listing of flashcards
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $search = $request->query('search', '');

        $flashcards = $this->flashcardService->paginate($perPage, $search);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.flashcard.list'),
            'data' => $flashcards->items(),
            'meta' => [
                'current_page' => $flashcards->currentPage(),
                'last_page' => $flashcards->lastPage(),
                'per_page' => $flashcards->perPage(),
                'total' => $flashcards->total(),
            ],
        ]);
    }

    /**
     * Store a newly created flashcard
     */
    public function store(StoreFlashcardRequest $request): JsonResponse
    {
        try {
            $flashcard = $this->flashcardService->create($request->validated());

            return response()->json([
                'status_code' => Response::HTTP_CREATED,
                'message' => __('messages.flashcard.created'),
                'data' => $flashcard,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.flashcard.create_error'));
        }
    }

    /**
     * Display the specified flashcard
     */
    public function show(string $id): JsonResponse
    {
        $flashcard = $this->flashcardService->find($id);

        if (!$flashcard) {
            return $this->errorResponse(__('messages.flashcard.not_found'), Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.flashcard.fetched'),
            'data' => $flashcard,
        ]);
    }

    /**
     * Update the specified flashcard
     */
    public function update(UpdateFlashcardRequest $request, string $id): JsonResponse
    {
        try {
            $flashcard = $this->flashcardService->find($id);

            if (!$flashcard) {
                return $this->errorResponse(__('messages.flashcard.not_found'), Response::HTTP_NOT_FOUND);
            }

            $updatedFlashcard = $this->flashcardService->update($flashcard, $request->validated());

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.flashcard.updated'),
                'data' => $updatedFlashcard,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.flashcard.update_error'));
        }
    }

    /**
     * Remove the specified flashcard
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $flashcard = $this->flashcardService->find($id);

            if (!$flashcard) {
                return $this->errorResponse(__('messages.flashcard.not_found'), Response::HTTP_NOT_FOUND);
            }

            $this->flashcardService->delete($flashcard);

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.flashcard.deleted'),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.flashcard.delete_error'));
        }
    }
}
