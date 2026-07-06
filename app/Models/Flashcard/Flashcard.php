<?php

namespace App\Models\Flashcard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\FlashcardCollection\FlashcardCollection;
use App\Models\WordType\WordType;

class Flashcard extends Model
{
    use HasFactory;

    protected $table = 'flashcard';

    protected $fillable = [
        'original_word',
        'translated_word',
        'explanation',
        'word_type_id',
    ];

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(
            FlashcardCollection::class,
            'flashcard_flashcard_collection',
            'flashcard_id',
            'collection_id'
        );
    }

    public function wordType(): BelongsTo
    {
        return $this->belongsTo(WordType::class, 'word_type_id');
    }
}
