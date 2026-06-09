<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionTestResource extends JsonResource
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
            'test_type_id' => $this->test_type_id,
            'collection_id' => $this->collection_id,
            'test_name' => $this->test_name,
            'total_questions' => $this->total_questions,
            'duration' => $this->duration,
            'status' => $this->status,
            'started_at' => optional($this->started_at)->toDateTimeString(),
            'finished_at' => optional($this->finished_at)->toDateTimeString(),
            'question_ids' => $this->whenLoaded('questions', function () {
                return $this->questions->pluck('id')->values()->all();
            }),
            'test_type' => new TestTypeResource($this->whenLoaded('testType')),
            'collection' => new FlashcardCollectionResource($this->whenLoaded('collection')),
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
