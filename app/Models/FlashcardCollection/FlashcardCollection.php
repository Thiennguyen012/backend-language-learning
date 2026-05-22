<?php

namespace App\Models\FlashcardCollection;

use App\Models\Flashcard\Flashcard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FlashcardCollection extends Model
{
    use HasFactory;

    protected $table = 'flashcard_collection';

    protected $fillable = [
        'collection_name',
        'description',
    ];

    public function flashcards(): BelongsToMany
    {
        return $this->belongsToMany(
            Flashcard::class,
            'flashcard_flashcard_collection',
            'collection_id',
            'flashcard_id'
        );
    }
}
