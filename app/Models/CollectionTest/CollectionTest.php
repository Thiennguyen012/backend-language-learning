<?php

namespace App\Models\CollectionTest;

use App\Models\FlashcardCollection\FlashcardCollection;
use App\Models\Question\Question;
use App\Models\TestType\TestType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollectionTest extends Model
{
    use HasFactory;

    protected $table = 'collection_test';

    protected $fillable = [
        'test_type_id',
        'collection_id',
        'test_name',
        'description',
        'total_questions',
        'duration',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'duration' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $appends = [
        'question_ids',
    ];


    public function testType(): BelongsTo
    {
        return $this->belongsTo(TestType::class, 'test_type_id');
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(FlashcardCollection::class, 'collection_id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(
            Question::class,
            'test_question',
            'collection_test_id',
            'question_id'
        );
    }

    public function getQuestionIdsAttribute(): array
    {
        if ($this->relationLoaded('questions')) {
            return $this->questions->pluck('id')->values()->all();
        }

        return $this->questions()->pluck('questions.id')->values()->all();
    }
}
