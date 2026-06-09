<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
            // 'question_type_id' => $this->question_type_id,
            'question_type' => $this->whenLoaded('questionType', function () {
                return [
                    'id' => $this->questionType->id,
                    'question_type_name' => $this->questionType->question_type_name,
                    'keyword' => $this->questionType->keyword,
                    'description' => $this->questionType->description,
                ];
            }),
            'question_text' => $this->question_text,
            'question_data' => $this->question_data,
            'flashcard_reference_ids' => $this->flashcard_reference_ids,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
