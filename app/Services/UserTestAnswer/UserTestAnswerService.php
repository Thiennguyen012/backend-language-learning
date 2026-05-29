<?php

namespace App\Services\UserTestAnswer;

use App\CPU\Helpers;
use App\Repositories\UserTestAnswer\UserTestAnswerInterface;

class UserTestAnswerService
{
    protected $userTestAnswerRepository;

    public function __construct(UserTestAnswerInterface $userTestAnswerRepository)
    {
        $this->userTestAnswerRepository = $userTestAnswerRepository;
    }

    /**
     * Get paginated user test answers
     */
    public function paginate($limit = Helpers::LIMIT_PER_PAGE, $search = '')
    {
        $where = [];
        $orderBy = ['created_at' => 'desc'];

        if ($search !== '') {
            $where['orWhere'] = [
                'user_test_attempt_id' => ['user_test_attempt_id', 'like', '%' . $search . '%'],
                'question_id' => ['question_id', 'like', '%' . $search . '%'],
                'user_answer' => ['user_answer', 'like', '%' . $search . '%'],
            ];
        }

        return $this->userTestAnswerRepository->paginate($where, $orderBy, ['*'], [], $limit);
    }

    public function create($data)
    {
        return $this->userTestAnswerRepository->create($data);
    }

    public function update($answer, $data)
    {
        return $this->userTestAnswerRepository->edit($answer, $data);
    }

    public function find($id, $with = [])
    {
        if (!empty($with)) {
            return $this->userTestAnswerRepository->first(['id' => $id], [], ['*'], $with);
        }
        return $this->userTestAnswerRepository->find($id);
    }

    public function findByAttemptAndQuestion($attemptId, $questionId)
    {
        return $this->userTestAnswerRepository->first([
            'user_test_attempt_id' => $attemptId,
            'question_id' => $questionId,
        ], [], ['*']);
    }

    public function delete($answer)
    {
        return $this->userTestAnswerRepository->delete($answer);
    }
}
