<?php

namespace App\Services\UserTestAttempt;

use App\CPU\Helpers;
use App\Models\UserTestAttempt\UserTestAttempt;
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

    public function finalizeAttempt(UserTestAttempt $attempt): UserTestAttempt
    {
        $attempt->loadMissing('answers');

        $correctCount = $attempt->answers->where('is_correct', true)->count();
        $totalScore = $correctCount;

        $startedAt = $attempt->started_time ?? $attempt->created_at;
        $elapsedSeconds = now()->diffInSeconds($startedAt);
        $finishedTime = now();

        $data = [
            'status' => 2,
            'correct_count' => $correctCount,
            'total_score' => $totalScore,
            'finished_time' => $finishedTime,
            'total_time' => $this->secondsToTime($elapsedSeconds),
        ];

        return $this->userTestAttemptRepository->edit($attempt, $data);
    }

    private function secondsToTime(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        $remaining = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remaining);
    }
}
