<?php

namespace App\Models\UserTestAnswer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\UserTestAttempt\UserTestAttempt;
use App\Models\Question\Question;

class UserTestAnswer extends Model
{
    use HasFactory;

    protected $table = 'user_test_answers';

    protected $fillable = [
        'user_test_attempt_id',
        'question_id',
        'user_answer',
        'is_correct',
    ];

    protected $casts = [
        'user_test_attempt_id' => 'integer',
        'question_id' => 'integer',
        'is_correct' => 'boolean',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(UserTestAttempt::class, 'user_test_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
