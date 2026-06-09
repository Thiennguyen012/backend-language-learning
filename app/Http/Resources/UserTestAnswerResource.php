<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTestAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_test_attempt_id' => $this->user_test_attempt_id,
            'question_id' => $this->question_id,
            'user_answer' => $this->user_answer,
            'is_correct' => $this->is_correct,
            'attempt' => $this->whenLoaded('attempt', function () {
                return [
                    'id' => $this->attempt->id,
                    'user_id' => $this->attempt->user_id,
                    'collection_test_id' => $this->attempt->collection_test_id,
                    'status' => $this->attemptStatusText($this->attempt->status),
                    'correct_count' => $this->attempt->correct_count,
                    'total_score' => $this->attempt->total_score,
                    'started_time' => optional($this->attempt->started_time)->toDateTimeString(),
                    'finished_time' => optional($this->attempt->finished_time)->toDateTimeString(),
                    'expired_at' => optional($this->attempt->expired_at)->toDateTimeString(),
                ];
            }),
            'question' => new QuestionResource($this->whenLoaded('question')),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }

    private function attemptStatusText(int $status): string
    {
        return match ($status) {
            1 => 'in_progress',
            2 => 'submitted',
            default => 'unknown',
        };
    }
}
