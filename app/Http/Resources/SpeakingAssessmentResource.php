<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpeakingAssessmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'status' => $this->status,
            'reference_text' => $this->reference_text,
            'dialect' => $this->dialect,
            'transcript' => $this->transcript,
            'result' => $this->result,
            'error_code' => $this->when($this->status === 'failed', $this->error_code),
            'audio_url' => route('speaking-assessments.audio', $this->uuid),
            'created_at' => $this->created_at?->toDateTimeString(),
            'processed_at' => $this->processed_at?->toDateTimeString(),
        ];
    }
}
