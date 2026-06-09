<?php

namespace App\Services\CollectionTest;

use App\CPU\Helpers;
use App\Repositories\CollectionTest\CollectionTestInterface;
use Illuminate\Support\Facades\DB;

class CollectionTestService
{
    protected $collectionTestRepository;

    public function __construct(CollectionTestInterface $collectionTestRepository)
    {
        $this->collectionTestRepository = $collectionTestRepository;
    }

    /**
     * Get paginated collection tests
     */
    public function paginate($limit = Helpers::LIMIT_PER_PAGE, $search = '')
    {
        $where = [];
        $orderBy = ['created_at' => 'desc'];

        if ($search) {
            $where['orWhere'] = [
                'test_name' => ['test_name', 'like', '%' . $search . '%'],
            ];
        }

        return $this->collectionTestRepository->paginate($where, $orderBy, ['*'], ['testType', 'collection'], $limit);
    }

    /**
     * Find collection test by ID
     */
    public function find($id, $with = [])
    {
        if (!empty($with)) {
            return $this->collectionTestRepository->first(['id' => $id], [], ['*'], $with);
        }

        return $this->collectionTestRepository->first(['id' => $id], [], ['*'], ['testType', 'collection', 'questions']);
    }

    /**
     * Create a new collection test
     */
    public function create($data)
    {
        $questionIds = $data['question_ids'] ?? null;
        unset($data['question_ids']);

        if (is_array($questionIds)) {
            $data['total_questions'] = count($questionIds);
        }

        return DB::transaction(function () use ($data, $questionIds) {
            $collectionTest = $this->collectionTestRepository->create($data);

            if (is_array($questionIds)) {
                $collectionTest->questions()->sync($questionIds);
            }

            return $collectionTest->load(['testType', 'collection', 'questions']);
        });
    }

    /**
     * Update collection test
     */
    public function update($collectionTest, $data)
    {
        $hasQuestionIds = array_key_exists('question_ids', $data);
        $questionIds = $data['question_ids'] ?? null;
        unset($data['question_ids']);

        if ($hasQuestionIds && is_array($questionIds)) {
            $data['total_questions'] = count($questionIds);
        }

        return DB::transaction(function () use ($collectionTest, $data, $hasQuestionIds, $questionIds) {
            $updatedCollectionTest = $this->collectionTestRepository->edit($collectionTest, $data);

            if ($hasQuestionIds) {
                $updatedCollectionTest->questions()->sync($questionIds ?? []);
            }

            return $updatedCollectionTest->load(['testType', 'collection', 'questions']);
        });
    }

    /**
     * Delete collection test
     */
    public function delete($collectionTest)
    {
        return $this->collectionTestRepository->delete($collectionTest);
    }
}
