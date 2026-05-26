<?php

namespace App\Models\Question;

use App\Models\CollectionTest\CollectionTest;
use App\Models\QuestionType\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';

    protected $fillable = [
        'question_type_id',
        'question_text',
        'question_data',
        'flashcard_reference_ids',
    ];

    protected $casts = [
        'question_data' => 'array',
        'flashcard_reference_ids' => 'array',
    ];

    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }

    public function collectionTests(): BelongsToMany
    {
        return $this->belongsToMany(
            CollectionTest::class,
            'test_question',
            'question_id',
            'collection_test_id'
        );
    }
}
