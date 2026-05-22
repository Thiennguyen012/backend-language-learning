<?php

namespace App\Services\FlashcardCollection;

use App\CPU\Helpers;
use App\Repositories\FlashcardCollection\FlashcardCollectionInterface;

class FlashcardCollectionService
{
    protected $flashcardCollectionRepository;

    public function __construct(FlashcardCollectionInterface $flashcardCollectionRepository)
    {
        $this->flashcardCollectionRepository = $flashcardCollectionRepository;
    }

    /**
     * Get all flashcard collections
     */
    public function getAll($search = '')
    {
        $where = [];
        $orderBy = ['created_at' => 'desc'];

        if ($search) {
            $where['orWhere'] = [
                'collection_name' => ['collection_name', 'like', '%' . $search . '%'],
                'description' => ['description', 'like', '%' . $search . '%'],
            ];
        }

        return $this->flashcardCollectionRepository->get($where, $orderBy, ['*']);
    }

    /**
     * Get paginated flashcard collections
     */
    public function paginate($limit = Helpers::LIMIT_PER_PAGE, $search = '')
    {
        $where = [];
        $orderBy = ['created_at' => 'desc'];

        if ($search) {
            $where['orWhere'] = [
                'collection_name' => ['collection_name', 'like', '%' . $search . '%'],
                'description' => ['description', 'like', '%' . $search . '%'],
            ];
        }

        return $this->flashcardCollectionRepository->paginate($where, $orderBy, ['*'], [], $limit);
    }

    /**
     * Find flashcard collection by ID
     */
    public function find($id, $with = [])
    {
        if (!empty($with)) {
            return $this->flashcardCollectionRepository->first(['id' => $id], [], ['*'], $with);
        }

        return $this->flashcardCollectionRepository->find($id);
    }

    /**
     * Create a new flashcard collection
     */
    public function create($data)
    {
        return $this->flashcardCollectionRepository->create($data);
    }

    /**
     * Update flashcard collection
     */
    public function update($collection, $data)
    {
        return $this->flashcardCollectionRepository->edit($collection, $data);
    }

    /**
     * Delete flashcard collection
     */
    public function delete($collection)
    {
        return $this->flashcardCollectionRepository->delete($collection);
    }
}
