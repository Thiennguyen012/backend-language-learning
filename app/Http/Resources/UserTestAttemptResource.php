<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTestAttemptResource extends JsonResource
{
    private bool $compact = false;

    public function compact(): self
    {
        $this->compact = true;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'collection_test_id' => $this->collection_test_id,
            'status' => $this->statusText($this->status),
            'correct_count' => $this->correct_count,
            'total_score' => $this->total_score,
            'started_time' => optional($this->started_time)->toDateTimeString(),
            'finished_time' => optional($this->finished_time)->toDateTimeString(),
            'total_time' => $this->total_time,
            'expired_at' => optional($this->expired_at)->toDateTimeString(),
            'collection_test' => $this->collectionTestData(),
            'questions' => UserTestAttemptQuestionResource::collection($this->whenLoaded('attemptQuestions')),
            'created_at' => $this->when(!$this->compact, optional($this->created_at)->toDateTimeString()),
            'updated_at' => $this->when(!$this->compact, optional($this->updated_at)->toDateTimeString()),
        ];
    }

    private function collectionTestData()
    {
        return $this->whenLoaded('collectionTest', function () {
            if ($this->compact) {
                return [
                    'id' => $this->collectionTest->id,
                    'test_name' => $this->collectionTest->test_name,
                    'description' => $this->collectionTest->description,
                    'total_questions' => $this->collectionTest->total_questions,
                    'duration' => $this->collectionTest->duration,
                ];
            }

            return new CollectionTestResource($this->collectionTest);
        });
    }

    private function statusText(int $status): string
    {
        return match ($status) {
            1 => 'in_progress',
            2 => 'submitted',
            default => 'unknown',
        };
    }
}
