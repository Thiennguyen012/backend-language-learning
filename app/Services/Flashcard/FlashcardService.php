<?php

namespace App\Services\Flashcard;

use App\Repositories\Flashcard\FlashcardInterface;
use App\CPU\Helpers;

class FlashcardService
{
    protected $flashcardRepository;

    public function __construct(FlashcardInterface $flashcardRepository)
    {
        $this->flashcardRepository = $flashcardRepository;
    }

    /**
     * Get all flashcards
     */
    public function getAll($search = '')
    {
        $where = [];
        $orderBy = ['created_at' => 'desc'];

        if ($search) {
            $where['orWhere'] = [
                'original_word' => ['original_word', 'like', '%' . $search . '%'],
                'translated_word' => ['translated_word', 'like', '%' . $search . '%'],
            ];
        }

        return $this->flashcardRepository->get($where, $orderBy, ['*']);
    }

    /**
     * Get paginated flashcards
     */
    public function paginate($limit = Helpers::LIMIT_PER_PAGE, $search = '')
    {
        $where = [];
        $orderBy = ['created_at' => 'desc'];

        if ($search) {
            $where['orWhere'] = [
                'original_word' => ['original_word', 'like', '%' . $search . '%'],
                'translated_word' => ['translated_word', 'like', '%' . $search . '%'],
            ];
        }

        return $this->flashcardRepository->paginate($where, $orderBy, ['*'], [], $limit);
    }

    /**
     * Find flashcard by ID
     */
    public function find($id)
    {
        return $this->flashcardRepository->find($id);
    }

    /**
     * Create a new flashcard
     */
    public function create($data)
    {
        return $this->flashcardRepository->create($data);
    }

    /**
     * Update flashcard
     */
    public function update($model, $data)
    {
        return $this->flashcardRepository->edit($model, $data);
    }

    /**
     * Delete flashcard
     */
    public function delete($model)
    {
        return $this->flashcardRepository->delete($model);
    }
}
