<?php

namespace App\Jobs;

use App\Models\SpeakingAssessment\SpeakingLevelAssessment;
use App\Services\SpeakingAssessment\SpeakingLevelEvaluator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class EvaluateSpeakingLevelAnswer implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 620;

    public array $backoff = [10, 30];

    public function __construct(public int $assessmentId)
    {
        $this->onQueue('speech-assessments');
    }

    public function handle(SpeakingLevelEvaluator $evaluator): void
    {
        $assessment = SpeakingLevelAssessment::query()->find($this->assessmentId);

        if (! $assessment || in_array($assessment->status, [
            SpeakingLevelAssessment::STATUS_COMPLETED,
            SpeakingLevelAssessment::STATUS_FAILED,
        ], true)) {
            return;
        }

        $assessment->update([
            'status' => SpeakingLevelAssessment::STATUS_PROCESSING,
            'error_code' => null,
        ]);

        $result = $evaluator->evaluate($assessment);

        $assessment->update([
            'status' => SpeakingLevelAssessment::STATUS_COMPLETED,
            'transcript' => $result['transcript'],
            'result' => $result,
            'processed_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        SpeakingLevelAssessment::query()
            ->whereKey($this->assessmentId)
            ->where('status', '!=', SpeakingLevelAssessment::STATUS_COMPLETED)
            ->update([
                'status' => SpeakingLevelAssessment::STATUS_FAILED,
                'error_code' => 'SPEAKING_LEVEL_EVALUATION_FAILED',
                'processed_at' => now(),
            ]);
    }
}
