<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashcardCollectionResource extends JsonResource
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
            'collection_name' => $this->collection_name,
            'description' => $this->description,
            // 'flashcard_ids' => $this->whenLoaded('flashcards', function () {
            //     return $this->flashcards->pluck('id')->values()->all();
            // }),
            'flashcards' => FlashcardResource::collection($this->whenLoaded('flashcards')),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
