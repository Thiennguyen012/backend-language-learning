<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashcardResource extends JsonResource
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
            'original_word' => $this->original_word,
            'translated_word' => $this->translated_word,
            'explanation' => $this->explanation,
            // 'word_type_id' => $this->word_type_id,
            'word_type' => $this->whenLoaded('wordType', function () {
                return [
                    'id' => $this->wordType->id,
                    'type_name' => $this->wordType->type_name,
                ];
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
