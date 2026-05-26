<?php

namespace App\Services\TestType;

use App\CPU\Helpers;
use App\Repositories\TestType\TestTypeInterface;

class TestTypeService
{
    protected $testTypeRepository;

    public function __construct(TestTypeInterface $testTypeRepository)
    {
        $this->testTypeRepository = $testTypeRepository;
    }

    /**
     * Get paginated test types
     */
    public function paginate($limit = Helpers::LIMIT_PER_PAGE, $search = '')
    {
        $where = [];
        $orderBy = ['created_at' => 'desc'];

        if ($search) {
            $where['orWhere'] = [
                'test_type' => ['test_type', 'like', '%' . $search . '%'],
            ];
        }

        return $this->testTypeRepository->paginate($where, $orderBy, ['*'], [], $limit);
    }

    /**
     * Find test type by ID
     */
    public function find($id)
    {
        return $this->testTypeRepository->find($id);
    }

    /**
     * Create a new test type
     */
    public function create($data)
    {
        return $this->testTypeRepository->create($data);
    }

    /**
     * Update test type
     */
    public function update($testType, $data)
    {
        return $this->testTypeRepository->edit($testType, $data);
    }

    /**
     * Delete test type
     */
    public function delete($testType)
    {
        return $this->testTypeRepository->delete($testType);
    }
}
