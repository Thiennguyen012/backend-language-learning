<?php

namespace App\Jobs;

use App\Models\SpeakingAssessment\SpeakingAssessment;
use App\Services\SpeakingAssessment\PronunciationScorer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ScoreSpeakingAssessment implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 620;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30];

    public function __construct(public int $assessmentId)
    {
        $this->onQueue('speech-assessments');
    }

    public function handle(PronunciationScorer $client): void
    {
        $assessment = SpeakingAssessment::query()->find($this->assessmentId);

        if (! $assessment || in_array($assessment->status, [
            SpeakingAssessment::STATUS_COMPLETED,
            SpeakingAssessment::STATUS_FAILED,
        ], true)) {
            return;
        }

        $assessment->update([
            'status' => SpeakingAssessment::STATUS_PROCESSING,
            'error_code' => null,
        ]);

        $payload = $client->score($assessment);
        $textScore = $payload['text_score'];

        $assessment->update([
            'status' => SpeakingAssessment::STATUS_COMPLETED,
            'transcript' => $textScore['text'] ?? null,
            'result' => [
                'text_score' => $textScore,
                'speechace_score' => $textScore['speechace_score'] ?? null,
                'ielts_score' => $textScore['ielts_score'] ?? null,
                'pte_score' => $textScore['pte_score'] ?? null,
                'cefr_score' => $textScore['cefr_score'] ?? null,
                'provider_version' => $payload['version'] ?? null,
                'provider' => $payload['provider'] ?? 'speechace',
            ],
            'processed_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        SpeakingAssessment::query()
            ->whereKey($this->assessmentId)
            ->whereNotIn('status', [SpeakingAssessment::STATUS_COMPLETED])
            ->update([
                'status' => SpeakingAssessment::STATUS_FAILED,
                'error_code' => 'PRONUNCIATION_SCORING_FAILED',
                'processed_at' => now(),
            ]);
    }
}
