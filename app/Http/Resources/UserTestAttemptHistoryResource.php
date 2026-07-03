<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTestAttemptHistoryResource extends JsonResource
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
            'status' => $this->statusText($this->status),
            'collection_test' => $this->whenLoaded('collectionTest', function () {
                return [
                    'id' => $this->collectionTest->id,
                    'test_name' => $this->collectionTest->test_name,
                    'description' => $this->collectionTest->description,
                    'total_questions' => $this->collectionTest->total_questions,
                    'duration' => $this->collectionTest->duration,
                ];
            }),
            'result' => [
                'correct_count' => $this->correct_count,
                'total_score' => $this->total_score,
            ],
            'started_at' => optional($this->started_time)->toDateTimeString(),
            'submitted_at' => optional($this->finished_time)->toDateTimeString(),
            'total_time' => $this->total_time,
        ];
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
