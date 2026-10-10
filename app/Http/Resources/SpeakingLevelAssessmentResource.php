<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpeakingLevelAssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'topic' => $this->topic,
            'question' => $this->question,
            'dialect' => $this->dialect,
            'preparation_seconds' => $this->preparation_seconds,
            'response_seconds' => $this->response_seconds,
            'status' => $this->status,
            'transcript' => $this->transcript,
            'result' => $this->result,
            'error_code' => $this->when($this->status === 'failed', $this->error_code),
            'created_at' => $this->created_at?->toDateTimeString(),
            'answered_at' => $this->answered_at?->toDateTimeString(),
            'processed_at' => $this->processed_at?->toDateTimeString(),
        ];
    }
}
