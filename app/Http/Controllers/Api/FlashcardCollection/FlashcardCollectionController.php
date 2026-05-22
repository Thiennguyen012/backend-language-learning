<?php

namespace App\Http\Controllers\Api\FlashcardCollection;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\FlashcardCollection\StoreFlashcardCollectionRequest;
use App\Http\Requests\FlashcardCollection\UpdateFlashcardCollectionRequest;
use App\Models\FlashcardCollection\FlashcardCollection;
use App\Services\FlashcardCollection\FlashcardCollectionService;
use App\Traits\ValidatesRequestData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FlashcardCollectionController extends Controller
{
    use ValidatesRequestData;

    protected $flashcardCollectionService;

    public function __construct(FlashcardCollectionService $flashcardCollectionService)
    {
        $this->flashcardCollectionService = $flashcardCollectionService;
    }

    /**
     * Display a listing of flashcard collections
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $search = $request->query('search', '');

        $collections = $this->flashcardCollectionService->paginate($perPage, $search);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.flashcard_collection.list'),
            'data' => $collections->items(),
            'meta' => [
                'current_page' => $collections->currentPage(),
                'last_page' => $collections->lastPage(),
                'per_page' => $collections->perPage(),
                'total' => $collections->total(),
            ],
        ]);
    }

    /**
     * Store a newly created flashcard collection
     */
    public function store(StoreFlashcardCollectionRequest $request): JsonResponse
    {
        try {
            $collection = $this->flashcardCollectionService->create($request->validated());

            return response()->json([
                'status_code' => Response::HTTP_CREATED,
                'message' => __('messages.flashcard_collection.created'),
                'data' => $collection,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.flashcard_collection.create_error'));
        }
    }

    /**
     * Display the specified flashcard collection
     */
    public function show(string $id): JsonResponse
    {
        $collection = $this->flashcardCollectionService->find($id, ['flashcards']);

        if (!$collection) {
            return $this->errorResponse(__('messages.flashcard_collection.not_found'), Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.flashcard_collection.fetched'),
            'data' => $collection,
        ]);
    }

    /**
     * Update the specified flashcard collection
     */
    public function update(UpdateFlashcardCollectionRequest $request, string $id): JsonResponse
    {
        try {
            $collection = $this->flashcardCollectionService->find($id);

            if (!$collection) {
                return $this->errorResponse(__('messages.flashcard_collection.not_found'), Response::HTTP_NOT_FOUND);
            }

            $updatedCollection = $this->flashcardCollectionService->update($collection, $request->validated());

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.flashcard_collection.updated'),
                'data' => $updatedCollection,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.flashcard_collection.update_error'));
        }
    }

    /**
     * Remove the specified flashcard collection
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $collection = $this->flashcardCollectionService->find($id);

            if (!$collection) {
                return $this->errorResponse(__('messages.flashcard_collection.not_found'), Response::HTTP_NOT_FOUND);
            }

            $this->flashcardCollectionService->delete($collection);

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.flashcard_collection.deleted'),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.flashcard_collection.delete_error'));
        }
    }

    /**
     * Attach flashcards to a collection
     */
    public function attach(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'flashcard_ids' => ['required', 'array'],
            'flashcard_ids.*' => ['integer', 'exists:flashcard,id'],
        ]);

        /** @var FlashcardCollection|null $collection */
        $collection = FlashcardCollection::query()->find($id);

        if (!$collection) {
            return $this->errorResponse(__('messages.flashcard_collection.not_found'), Response::HTTP_NOT_FOUND);
        }

        $collection->flashcards()->syncWithoutDetaching($data['flashcard_ids']);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.flashcard_collection.attached'),
        ]);
    }

    /**
     * Detach flashcards from a collection
     */
    public function detach(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'flashcard_ids' => ['required', 'array'],
            'flashcard_ids.*' => ['integer', 'exists:flashcard,id'],
        ]);

        /** @var FlashcardCollection|null $collection */
        $collection = FlashcardCollection::query()->find($id);

        if (!$collection) {
            return $this->errorResponse(__('messages.flashcard_collection.not_found'), Response::HTTP_NOT_FOUND);
        }

        $collection->flashcards()->detach($data['flashcard_ids']);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.flashcard_collection.detached'),
        ]);
    }
}
