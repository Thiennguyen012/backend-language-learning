<?php

namespace App\Models\UserTestAttempt;

use App\Models\CollectionTest\CollectionTest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UserTestAnswer\UserTestAnswer;

class UserTestAttempt extends Model
{
    use HasFactory;

    protected $table = 'user_test_attempts';

    protected $fillable = [
        'user_id',
        'collection_test_id',
        'status',
        'correct_count',
        'total_score',
        'started_time',
        'finished_time',
        'total_time',
        'question_ids',
        'expired_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'collection_test_id' => 'integer',
        'status' => 'integer',
        'correct_count' => 'integer',
        'total_score' => 'integer',
        'started_time' => 'datetime',
        'finished_time' => 'datetime',
        'total_time' => 'string',
        'question_ids' => 'array',
        'expired_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function collectionTest(): BelongsTo
    {
        return $this->belongsTo(CollectionTest::class, 'collection_test_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(UserTestAnswer::class, 'user_test_attempt_id');
    }
}
