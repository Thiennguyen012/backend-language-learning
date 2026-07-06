<?php

namespace App\Services\Question;

use App\CPU\Helpers;
use App\Repositories\Question\QuestionInterface;

class QuestionService
{
    protected $questionRepository;

    public function __construct(QuestionInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    /**
     * Get paginated questions
     */
    public function paginate($limit = Helpers::LIMIT_PER_PAGE, $search = '')
    {
        $where = [];
        $orderBy = ['created_at' => 'desc'];

        if ($search) {
            $where['orWhere'] = [
                'question_text' => ['question_text', 'like', '%' . $search . '%'],
                'question_data' => ['question_data', 'like', '%' . $search . '%'],
            ];
        }

        return $this->questionRepository->paginate($where, $orderBy, ['*'], ['questionType'], $limit);
    }

    /**
     * Find question by ID
     */
    public function find($id, $with = [])
    {
        if (!empty($with)) {
            return $this->questionRepository->first(['id' => $id], [], ['*'], $with);
        }

        return $this->questionRepository->first(['id' => $id], [], ['*'], ['questionType']);
    }

    /**
     * Create a new question
     */
    public function create($data)
    {
        return $this->questionRepository->create($data)->load('questionType');
    }

    /**
     * Update question
     */
    public function update($question, $data)
    {
        return $this->questionRepository->edit($question, $data)->load('questionType');
    }

    /**
     * Delete question
     */
    public function delete($question)
    {
        return $this->questionRepository->delete($question);
    }
}
