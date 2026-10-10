<?php

namespace App\Models\SpeakingAssessment;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeakingLevelAssessment extends Model
{
    public const STATUS_AWAITING_ANSWER = 'awaiting_answer';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid',
        'user_id',
        'topic',
        'question',
        'dialect',
        'preparation_seconds',
        'response_seconds',
        'status',
        'audio_path',
        'transcript',
        'result',
        'error_code',
        'answered_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'array',
            'answered_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
