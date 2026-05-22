<?php

namespace App\Models\WordType;

use App\Models\Flashcard\Flashcard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordType extends Model
{
    use HasFactory;

    protected $table = 'word_type';

    protected $fillable = [
        'type_name',
    ];

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class, 'word_type_id');
    }
}
