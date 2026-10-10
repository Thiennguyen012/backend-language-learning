<?php

namespace App\Http\Controllers\Api\SpeakingAssessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpeakingAssessment\CreateSpeakingLevelAssessmentRequest;
use App\Http\Requests\SpeakingAssessment\SubmitSpeakingLevelAnswerRequest;
use App\Http\Resources\SpeakingLevelAssessmentResource;
use App\Jobs\EvaluateSpeakingLevelAnswer;
use App\Models\SpeakingAssessment\SpeakingLevelAssessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpeakingLevelAssessmentController extends Controller
{
    public function topics(): JsonResponse
    {
        $topics = collect(config('speaking_level.topics', []))
            ->map(fn (array $topic, string $id) => [
                'id' => $id,
                'label' => $topic['label'],
            ])
            ->values();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'Speaking topics retrieved.',
            'data' => $topics,
        ]);
    }

    public function store(CreateSpeakingLevelAssessmentRequest $request): JsonResponse
    {
        $topicId = $request->validated('topic');
        $topic = config('speaking_level.topics.'.$topicId);
        $question = collect($topic['questions'])->random();

        $assessment = SpeakingLevelAssessment::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'topic' => $topicId,
            'question' => $question,
            'dialect' => 'en-us',
            'preparation_seconds' => config('speaking_level.preparation_seconds', 15),
            'response_seconds' => config('speaking_level.response_seconds', 90),
            'status' => SpeakingLevelAssessment::STATUS_AWAITING_ANSWER,
        ]);

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'message' => 'Speaking assessment question created.',
            'data' => new SpeakingLevelAssessmentResource($assessment),
        ], Response::HTTP_CREATED);
    }

    public function submitAnswer(
        SubmitSpeakingLevelAnswerRequest $request,
        string $uuid
    ): JsonResponse {
        $assessment = $this->findOwnedAssessment($request->user()->id, $uuid);

        if (! $assessment) {
            return $this->notFound();
        }

        if ($assessment->status !== SpeakingLevelAssessment::STATUS_AWAITING_ANSWER) {
            return response()->json([
                'status_code' => Response::HTTP_CONFLICT,
                'message' => 'This speaking assessment already has an answer.',
            ], Response::HTTP_CONFLICT);
        }

        $audio = $request->file('audio');
        $path = $audio->storeAs(
            'speaking-level-assessments/'.$request->user()->id,
            $assessment->uuid.'.'.strtolower($audio->getClientOriginalExtension()),
            config('services.speaking_assessment.storage_disk')
        );

        if (! $path) {
            return response()->json([
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'The speaking answer audio could not be stored.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $assessment->update([
            'audio_path' => $path,
            'status' => SpeakingLevelAssessment::STATUS_QUEUED,
            'answered_at' => now(),
        ]);

        EvaluateSpeakingLevelAnswer::dispatch($assessment->id)->afterCommit();

        return response()->json([
            'status_code' => Response::HTTP_ACCEPTED,
            'message' => 'Speaking answer queued for evaluation.',
            'data' => new SpeakingLevelAssessmentResource($assessment->fresh()),
        ], Response::HTTP_ACCEPTED);
    }

    public function show(string $uuid): JsonResponse
    {
        $assessment = $this->findOwnedAssessment(request()->user()->id, $uuid);

        if (! $assessment) {
            return $this->notFound();
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'Speaking level assessment retrieved.',
            'data' => new SpeakingLevelAssessmentResource($assessment),
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $assessment = $this->findOwnedAssessment(request()->user()->id, $uuid);

        if (! $assessment) {
            return $this->notFound();
        }

        if ($assessment->audio_path) {
            Storage::disk(config('services.speaking_assessment.storage_disk'))
                ->delete($assessment->audio_path);
        }
        $assessment->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'Speaking level assessment deleted.',
        ]);
    }

    private function findOwnedAssessment(int $userId, string $uuid): ?SpeakingLevelAssessment
    {
        return SpeakingLevelAssessment::query()
            ->where('uuid', $uuid)
            ->where('user_id', $userId)
            ->first();
    }

    private function notFound(): JsonResponse
    {
        return response()->json([
            'status_code' => Response::HTTP_NOT_FOUND,
            'message' => 'Speaking level assessment not found.',
        ], Response::HTTP_NOT_FOUND);
    }
}
