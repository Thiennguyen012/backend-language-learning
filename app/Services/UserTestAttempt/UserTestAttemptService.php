<?php

namespace App\Services\UserTestAttempt;

use App\CPU\Helpers;
use App\Repositories\UserTestAttempt\UserTestAttemptInterface;

class UserTestAttemptService
{
    protected $userTestAttemptRepository;

    public function __construct(UserTestAttemptInterface $userTestAttemptRepository)
    {
        $this->userTestAttemptRepository = $userTestAttemptRepository;
    }

    /**
     * Get paginated user test attempts
     */
    public function paginate($limit = Helpers::LIMIT_PER_PAGE, $search = '')
    {
        $where = [];
        $orderBy = ['created_at' => 'desc'];

        if ($search !== '') {
            $where['orWhere'] = [
                'user_id' => ['user_id', 'like', '%' . $search . '%'],
                'collection_test_id' => ['collection_test_id', 'like', '%' . $search . '%'],
                'status' => ['status', 'like', '%' . $search . '%'],
            ];
        }

        return $this->userTestAttemptRepository->paginate($where, $orderBy, ['*'], [], $limit);
    }

    /**
     * Find user test attempt by ID
     */
    public function find($id, $with = [])
    {
        if (!empty($with)) {
            return $this->userTestAttemptRepository->first(['id' => $id], [], ['*'], $with);
        }

        return $this->userTestAttemptRepository->find($id);
    }

    /**
     * Create a new user test attempt
     */
    public function create($data)
    {
        return $this->userTestAttemptRepository->create($data);
    }

    /**
     * Update user test attempt
     */
    public function update($attempt, $data)
    {
        return $this->userTestAttemptRepository->edit($attempt, $data);
    }

    /**
     * Delete user test attempt
     */
    public function delete($attempt)
    {
        return $this->userTestAttemptRepository->delete($attempt);
    }
}
