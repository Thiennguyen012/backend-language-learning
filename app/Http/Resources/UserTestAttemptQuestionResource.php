<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTestAttemptQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $answer = $this->relationLoaded('attemptAnswer') ? $this->attemptAnswer : null;

        return [
            'id' => $this->id,
            'question_type' => $this->whenLoaded('questionType', function () {
                return $this->questionType->keyword;
            }),
            'question_text' => $this->question_text,
            'question_data' => $this->question_data,
            'answer' => $answer ? [
                'id' => $answer->id,
                'user_answer' => $answer->user_answer,
                'is_correct' => $answer->is_correct,
                'answered_at' => optional($answer->updated_at)->toDateTimeString(),
            ] : null,
        ];
    }
}
