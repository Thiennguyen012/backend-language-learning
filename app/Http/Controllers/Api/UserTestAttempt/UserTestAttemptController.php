<?php

namespace App\Http\Controllers\Api\UserTestAttempt;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Jobs\AutoSubmitUserTestAttempt;
use App\Http\Requests\UserTestAttempt\StoreUserTestAttemptRequest;
use App\Http\Requests\UserTestAttempt\UpdateUserTestAttemptRequest;
use App\Http\Resources\UserTestAnswerResource;
use App\Http\Resources\UserTestAttemptHistoryResource;
use App\Http\Resources\UserTestAttemptResource;
use App\Models\User;
use App\Models\CollectionTest\CollectionTest;
use App\Models\Question\Question;
use App\Models\UserTestAttempt\UserTestAttempt;
use App\Services\UserTestAnswer\UserTestAnswerService;
use App\Services\UserTestAttempt\UserTestAttemptService;
use App\Traits\ValidatesRequestData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserTestAttemptController extends Controller
{
    use ValidatesRequestData;

    protected $userTestAttemptService;
    protected $userTestAnswerService;

    public function __construct(UserTestAttemptService $userTestAttemptService, UserTestAnswerService $userTestAnswerService)
    {
        $this->userTestAttemptService = $userTestAttemptService;
        $this->userTestAnswerService = $userTestAnswerService;
    }

    /**
     * Display a listing of user test attempts
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $search = $request->query('search', '');

        $attempts = $this->userTestAttemptService->paginate($perPage, $search);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.user_test_attempt')]),
            'data' => UserTestAttemptResource::collection($attempts->getCollection()),
            'meta' => [
                'current_page' => $attempts->currentPage(),
                'last_page' => $attempts->lastPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user test attempt
     */
    public function store(StoreUserTestAttemptRequest $request): JsonResponse
    {
        try {
            $attempt = $this->userTestAttemptService->create($request->validated());

            return response()->json([
                'status_code' => Response::HTTP_CREATED,
                'message' => __('messages.common.created', ['entity' => __('messages.entities.user_test_attempt')]),
                'data' => new UserTestAttemptResource($attempt),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.create_error', ['entity' => __('messages.entities.user_test_attempt')]));
        }
    }

    /**
     * Display the specified user test attempt
     */
    public function show(string $id): JsonResponse
    {
        $attempt = $this->userTestAttemptService->find($id, ['collectionTest', 'answers']);

        if (!$attempt) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                Response::HTTP_NOT_FOUND
            );
        }

        $attempt->setRelation('attemptQuestions', $this->getAttemptQuestions($attempt));

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.user_test_attempt')]),
            'data' => (new UserTestAttemptResource($attempt))->compact(),
        ]);
    }

    public function historyByUser(Request $request, string $userId): JsonResponse
    {
        $user = User::query()->find($userId);

        if (!$user) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.user')]),
                Response::HTTP_NOT_FOUND
            );
        }

        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $attempts = $this->userTestAttemptService->paginateByUser($user->id, $perPage);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.user_test_attempt')]),
            'data' => UserTestAttemptHistoryResource::collection($attempts->getCollection()),
            'meta' => [
                'current_page' => $attempts->currentPage(),
                'last_page' => $attempts->lastPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
            ],
        ]);
    }

    public function myAttempts(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', Helpers::LIMIT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, Helpers::LIMIT_PER_PAGE) : Helpers::LIMIT_PER_PAGE;
        $attempts = $this->userTestAttemptService->paginateByUser($request->user()->id, $perPage);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.user_test_attempt')]),
            'data' => UserTestAttemptHistoryResource::collection($attempts->getCollection()),
            'meta' => [
                'current_page' => $attempts->currentPage(),
                'last_page' => $attempts->lastPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
            ],
        ]);
    }

    /**
     * Update the specified user test attempt
     */
    public function update(UpdateUserTestAttemptRequest $request, string $id): JsonResponse
    {
        try {
            $attempt = $this->userTestAttemptService->find($id);

            if (!$attempt) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $updatedAttempt = $this->userTestAttemptService->update($attempt, $request->validated());

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.updated', ['entity' => __('messages.entities.user_test_attempt')]),
                'data' => new UserTestAttemptResource($updatedAttempt),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.update_error', ['entity' => __('messages.entities.user_test_attempt')]));
        }
    }

    /**
     * Remove the specified user test attempt
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $attempt = $this->userTestAttemptService->find($id);

            if (!$attempt) {
                return $this->errorResponse(
                    __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->userTestAttemptService->delete($attempt);

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.deleted', ['entity' => __('messages.entities.user_test_attempt')]),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __('messages.common.delete_error', ['entity' => __('messages.entities.user_test_attempt')]));
        }
    }

    /**
     * Start a test attempt and return attempt id with expires time
     */
    public function start(Request $request, string $id): JsonResponse
    {
        $userId = auth('sanctum')->id();

        if (!$userId) {
            return $this->errorResponse(__('messages.common.not_found', ['entity' => __('messages.entities.user')]), Response::HTTP_UNAUTHORIZED);
        }


        $collectionTest = CollectionTest::query()->find($id);

        if (!$collectionTest) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.collection_test')]),
                Response::HTTP_NOT_FOUND
            );
        }

        $now = now();
        if ($collectionTest->started_at && $collectionTest->started_at->isFuture()) {
            return $this->errorResponse(__('messages.collection_test.not_started'), Response::HTTP_CONFLICT);
        }

        if ($collectionTest->finished_at && $collectionTest->finished_at->isPast()) {
            return $this->errorResponse(__('messages.collection_test.expired'), Response::HTTP_CONFLICT);
        }

        $existingAttempt = UserTestAttempt::query()
            ->where('user_id', $userId)
            ->where('collection_test_id', $collectionTest->id)
            ->where('status', 1)
            ->latest('id')
            ->first();

        if ($existingAttempt) {
            $existingAttempt->load('answers');
            $remainingSeconds = $this->getRemainingSeconds($existingAttempt);

            if ($remainingSeconds !== null && $remainingSeconds <= 0) {
                $this->userTestAttemptService->finalizeAttempt($existingAttempt);
            } else {
                return response()->json([
                    'status_code' => Response::HTTP_OK,
                    'message' => __('messages.common.fetched', ['entity' => __('messages.entities.user_test_attempt')]),
                    'data' => [
                        'attempt_id' => $existingAttempt->id,
                        'expires_at' => optional($existingAttempt->expired_at)->toDateTimeString(),
                        'remaining_seconds' => $remainingSeconds,
                    ],
                ], Response::HTTP_OK);
            }
        }

        $totalTime = null;
        $startedTime = now();
        $expiresAt = null;

        $durationMinutes = $collectionTest->duration;
        if (is_numeric($durationMinutes) && (int) $durationMinutes > 0) {
            $expiresAt = $startedTime->copy()->addMinutes((int) $durationMinutes)->toDateTimeString();
        }

        $attempt = $this->userTestAttemptService->create([
            'user_id' => $userId,
            'collection_test_id' => $collectionTest->id,
            'status' => 1,
            'correct_count' => 0,
            'total_score' => 0,
            'started_time' => $startedTime,
            'total_time' => $totalTime,
            'expired_at' => $expiresAt,
        ]);

        $questionQuery = $collectionTest->questions();
        $totalQuestions = (int) ($collectionTest->total_questions ?? 0);
        if ($totalQuestions > 0) {
            $questionQuery = $questionQuery->inRandomOrder()->take($totalQuestions);
        } else {
            $questionQuery = $questionQuery->inRandomOrder();
        }

        $questionIds = $questionQuery->pluck('questions.id')->values()->all();
        if (!empty($questionIds)) {
            $this->userTestAttemptService->update($attempt, [
                'question_ids' => $questionIds,
            ]);
        }

        if ($attempt->expired_at) {
            AutoSubmitUserTestAttempt::dispatch($attempt->id)->delay($attempt->expired_at);
        }

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'message' => __('messages.common.created', ['entity' => __('messages.entities.user_test_attempt')]),
            'data' => [
                'attempt_id' => $attempt->id,
                'expires_at' => $expiresAt,
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Get remaining seconds for an attempt
     */
    public function remaining(string $id): JsonResponse
    {
        $userId = auth('sanctum')->id();

        if (!$userId) {
            return $this->errorResponse(__('messages.common.not_found', ['entity' => __('messages.entities.user')]), Response::HTTP_UNAUTHORIZED);
        }

        $attempt = UserTestAttempt::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->with(['collectionTest', 'answers'])
            ->first();

        if (!$attempt) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                Response::HTTP_NOT_FOUND
            );
        }

        $remainingSeconds = (int) $attempt->status === UserTestAttempt::STATUS_SUBMITTED
            ? 0
            : $this->getRemainingSeconds($attempt);

        $attempt->setRelation('attemptQuestions', $this->getAttemptQuestions($attempt));

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.user_test_attempt')]),
            'data' => [
                'attempt' => (new UserTestAttemptResource($attempt))->compact(),
                'remaining_seconds' => $remainingSeconds,
            ],
        ]);
    }

    /**
     * Get questions for an attempt (randomized on first call)
     */
    public function questions(string $id): JsonResponse
    {
        $userId = auth('sanctum')->id();

        if (!$userId) {
            return $this->errorResponse(__('messages.common.not_found', ['entity' => __('messages.entities.user')]), Response::HTTP_UNAUTHORIZED);
        }

        $attempt = UserTestAttempt::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->with(['collectionTest', 'answers'])
            ->first();

        if (!$attempt) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                Response::HTTP_NOT_FOUND
            );
        }

        $collectionTest = $attempt->collectionTest;

        if (!$collectionTest) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.collection_test')]),
                Response::HTTP_NOT_FOUND
            );
        }

        $questionIds = is_array($attempt->question_ids) ? $attempt->question_ids : [];

        if (empty($questionIds)) {
            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => __('messages.common.fetched', ['entity' => __('messages.entities.question')]),
                'data' => [],
            ]);
        }

        $questions = Question::query()->whereIn('id', $questionIds)->get();
        $positions = array_flip($questionIds);
        $orderedQuestions = $questions->sortBy(function ($question) use ($positions) {
            return $positions[$question->id] ?? PHP_INT_MAX;
        })->values();

        $answersByQuestion = $attempt->answers->keyBy('question_id');
        $payload = $orderedQuestions->map(function ($question) use ($answersByQuestion) {
            $data = $question->toArray();
            $answer = $answersByQuestion->get($question->id);

            if ($answer) {
                $data['user_answer'] = $answer->user_answer;
                $data['is_correct'] = $answer->is_correct;
            }

            return $data;
        })->all();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.fetched', ['entity' => __('messages.entities.question')]),
            'data' => $payload,
        ]);
    }

    /**
     * Auto-save answer for an attempt
     */
    public function answer(Request $request, string $id): JsonResponse
    {
        $payload = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'user_answer' => 'required',
        ]);

        $userId = auth('sanctum')->id();

        if (!$userId) {
            return $this->errorResponse(__('messages.common.not_found', ['entity' => __('messages.entities.user')]), Response::HTTP_UNAUTHORIZED);
        }

        $attempt = UserTestAttempt::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$attempt) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                Response::HTTP_NOT_FOUND
            );
        }

        if ((int) $attempt->status !== UserTestAttempt::STATUS_IN_PROGRESS) {
            return $this->errorResponse(
                __('messages.user_test_attempt.ended'),
                Response::HTTP_CONFLICT
            );
        }

        if (is_array($attempt->question_ids) && !in_array((int) $payload['question_id'], $attempt->question_ids, true)) {
            return $this->errorResponse(__('messages.common.not_found', ['entity' => __('messages.entities.question')]), Response::HTTP_NOT_FOUND);
        }

        $question = Question::query()->with('questionType')->find($payload['question_id']);

        if (!$question) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.question')]),
                Response::HTTP_NOT_FOUND
            );
        }

        $userAnswer = $payload['user_answer'];
        $normalizedAnswer = $this->normalizeUserAnswer($question, $userAnswer);
        $isCorrect = $this->isAnswerCorrect($question, $normalizedAnswer);
        $answerValue = is_string($normalizedAnswer) ? $normalizedAnswer : json_encode($normalizedAnswer, JSON_UNESCAPED_UNICODE);

        $data = [
            'user_test_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'user_answer' => $answerValue,
            'is_correct' => $isCorrect ? 1 : 0,
        ];

        $existing = $this->userTestAnswerService->findByAttemptAndQuestion($attempt->id, $question->id);
        $answer = $existing
            ? $this->userTestAnswerService->update($existing, $data)
            : $this->userTestAnswerService->create($data);

        return response()->json([
            'status_code' => $existing ? Response::HTTP_OK : Response::HTTP_CREATED,
            'message' => $existing
                ? __('messages.common.updated', ['entity' => __('messages.entities.user_test_answer')])
                : __('messages.common.created', ['entity' => __('messages.entities.user_test_answer')]),
            'data' => new UserTestAnswerResource($answer),
        ], $existing ? Response::HTTP_OK : Response::HTTP_CREATED);
    }

    /**
     * Submit an attempt and finalize score
     */
    public function submit(string $id): JsonResponse
    {
        $userId = auth('sanctum')->id();

        if (!$userId) {
            return $this->errorResponse(__('messages.common.not_found', ['entity' => __('messages.entities.user')]), Response::HTTP_UNAUTHORIZED);
        }

        $attempt = UserTestAttempt::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->with('answers')
            ->first();

        if (!$attempt) {
            return $this->errorResponse(
                __('messages.common.not_found', ['entity' => __('messages.entities.user_test_attempt')]),
                Response::HTTP_NOT_FOUND
            );
        }

        if ((int) $attempt->status !== UserTestAttempt::STATUS_IN_PROGRESS) {
            return $this->errorResponse(
                __('messages.user_test_attempt.ended'),
                Response::HTTP_CONFLICT
            );
        }

        $attempt = $this->userTestAttemptService->finalizeAttempt($attempt);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.updated', ['entity' => __('messages.entities.user_test_attempt')]),
            'data' => new UserTestAttemptResource($attempt),
        ]);
    }

    private function timeToSeconds(string $time): ?int
    {
        $parts = explode(':', $time);
        if (count($parts) !== 3) {
            return null;
        }

        [$hours, $minutes, $seconds] = $parts;

        if (!is_numeric($hours) || !is_numeric($minutes) || !is_numeric($seconds)) {
            return null;
        }

        return ((int) $hours * 3600) + ((int) $minutes * 60) + (int) $seconds;
    }

    private function getAttemptQuestions(UserTestAttempt $attempt)
    {
        $questionIds = is_array($attempt->question_ids) ? $attempt->question_ids : [];

        if (empty($questionIds)) {
            return collect();
        }

        $questions = Question::query()
            ->with('questionType')
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $answersByQuestion = $attempt->answers->keyBy('question_id');

        return collect($questionIds)
            ->map(function ($questionId) use ($questions, $answersByQuestion) {
                $question = $questions->get((int) $questionId);

                if (!$question) {
                    return null;
                }

                $question->setRelation('attemptAnswer', $answersByQuestion->get((int) $questionId));

                return $question;
            })
            ->filter()
            ->values();
    }

    private function getRemainingSeconds($attempt): ?int
    {
        if ($attempt->expired_at) {
            return max(0, now()->diffInSeconds($attempt->expired_at, false));
        }

        if (!is_string($attempt->total_time) || $attempt->total_time === '') {
            return null;
        }

        $totalSeconds = $this->timeToSeconds($attempt->total_time);
        if ($totalSeconds === null) {
            return null;
        }

        $startedAt = $attempt->started_time ?? $attempt->created_at;
        $elapsedSeconds = now()->diffInSeconds($startedAt);
        return max(0, $totalSeconds - $elapsedSeconds);
    }

    private function isAnswerCorrect(Question $question, $userAnswer): bool
    {
        $type = strtolower((string) optional($question->questionType)->keyword);
        $data = $question->question_data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }
        $data = is_array($data) ? $data : [];

        switch ($type) {
            case 'multiple_choice':
                return $this->checkMultipleChoice($data, $userAnswer);
            case 'true_false':
                return $this->checkTrueFalse($data, $userAnswer);
            case 'fill_in_blank':
                return $this->checkFillBlank($data, $userAnswer);
            case 'matching':
                return $this->checkMatching($data, $userAnswer);
            default:
                return false;
        }
    }

    private function normalizeUserAnswer(Question $question, $userAnswer)
    {
        $type = strtolower((string) optional($question->questionType)->keyword);
        $data = $question->question_data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }
        $data = is_array($data) ? $data : [];

        switch ($type) {
            case 'multiple_choice':
                return $this->normalizeMultipleChoice($data, $userAnswer);
            case 'true_false':
                return $this->normalizeTrueFalse($userAnswer);
            case 'fill_in_blank':
                return $this->normalizeFillBlank($userAnswer);
            case 'matching':
                return $this->normalizeMatching($userAnswer);
            default:
                return $userAnswer;
        }
    }

    private function checkMultipleChoice(array $data, $userAnswer): bool
    {
        if (!array_key_exists('correct', $data)) {
            return false;
        }

        $correct = $data['correct'];

        if (is_int($correct) || is_numeric($correct)) {
            return (int) $userAnswer === (int) $correct;
        }

        if (is_string($correct)) {
            return $this->normalizeString($userAnswer) === $this->normalizeString($correct);
        }

        return false;
    }

    private function checkTrueFalse(array $data, $userAnswer): bool
    {
        if (!array_key_exists('correct', $data)) {
            return false;
        }

        $correct = $data['correct'];
        if (!is_bool($correct)) {
            return false;
        }

        $userValue = $this->toBoolean($userAnswer);
        if ($userValue === null) {
            return false;
        }

        return $userValue === $correct;
    }

    private function checkFillBlank(array $data, $userAnswer): bool
    {
        if (!isset($data['answer']) || !is_string($data['answer'])) {
            return false;
        }

        $expected = $this->normalizeString($data['answer']);
        $actual = $this->normalizeString($userAnswer);

        return $actual !== '' && $expected === $actual;
    }

    private function checkMatching(array $data, $userAnswer): bool
    {
        if (!isset($data['pairs']) || !is_array($data['pairs'])) {
            return false;
        }

        $expected = $this->pairsToMap($data['pairs']);
        $actual = $this->pairsToMap($userAnswer);

        if ($expected === null || $actual === null) {
            return false;
        }

        return $expected === $actual;
    }

    private function normalizeMultipleChoice(array $data, $userAnswer)
    {
        if (is_int($userAnswer) || is_numeric($userAnswer)) {
            return (int) $userAnswer;
        }

        if (is_string($userAnswer)) {
            return trim($userAnswer);
        }

        return $userAnswer;
    }

    private function normalizeTrueFalse($userAnswer)
    {
        $value = $this->toBoolean($userAnswer);
        return $value === null ? $userAnswer : $value;
    }

    private function normalizeFillBlank($userAnswer): string
    {
        return $this->normalizeString($userAnswer);
    }

    private function normalizeMatching($userAnswer)
    {
        $map = $this->pairsToMap($userAnswer);
        if ($map === null) {
            return $userAnswer;
        }

        $pairs = [];
        foreach ($map as $left => $right) {
            $pairs[] = [
                'left' => $left,
                'right' => $right,
            ];
        }

        return $pairs;
    }

    private function pairsToMap($pairs): ?array
    {
        if (is_string($pairs)) {
            $decoded = json_decode($pairs, true);
            if (is_array($decoded)) {
                $pairs = $decoded;
            }
        }

        if (!is_array($pairs)) {
            return null;
        }

        $map = [];
        foreach ($pairs as $pair) {
            if (is_array($pair) && isset($pair['left'], $pair['right'])) {
                $left = (string) $pair['left'];
                $right = (string) $pair['right'];
                $map[$left] = $right;
                continue;
            }

            if (is_array($pair) && count($pair) === 2 && array_is_list($pair)) {
                $left = (string) $pair[0];
                $right = (string) $pair[1];
                $map[$left] = $right;
                continue;
            }

            return null;
        }

        ksort($map);
        return $map;
    }

    private function toBoolean($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['true', '1'], true)) {
                return true;
            }
            if (in_array($normalized, ['false', '0'], true)) {
                return false;
            }
        }

        return null;
    }

    private function normalizeString($value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($text, 'UTF-8');
        }

        return strtolower($text);
    }
}
