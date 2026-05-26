<?php

namespace App\Models\UserTestAttempt;

use App\Models\CollectionTest\CollectionTest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'user_id' => 'integer',
        'collection_test_id' => 'integer',
        'status' => 'integer',
        'correct_count' => 'integer',
        'total_score' => 'integer',
        'started_time' => 'string',
        'finished_time' => 'string',
        'total_time' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function collectionTest(): BelongsTo
    {
        return $this->belongsTo(CollectionTest::class, 'collection_test_id');
    }
}
