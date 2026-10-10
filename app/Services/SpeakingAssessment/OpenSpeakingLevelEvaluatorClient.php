<?php

namespace App\Services\SpeakingAssessment;

use App\Models\SpeakingAssessment\SpeakingLevelAssessment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OpenSpeakingLevelEvaluatorClient implements SpeakingLevelEvaluator
{
    public function evaluate(SpeakingLevelAssessment $assessment): array
    {
        $disk = Storage::disk(config('services.speaking_assessment.storage_disk'));
        $stream = $disk->readStream($assessment->audio_path);

        if (! is_resource($stream)) {
            throw new RuntimeException('The speaking answer audio file is unavailable.');
        }

        $audio = stream_get_contents($stream);
        fclose($stream);

        if (! is_string($audio) || $audio === '') {
            throw new RuntimeException('The speaking answer audio file is empty.');
        }

        try {
            $response = Http::connectTimeout(10)
                ->timeout((int) config('services.openpronounce.timeout', 600))
                ->attach('file', $audio, basename($assessment->audio_path))
                ->post(rtrim((string) config('services.openpronounce.url'), '/').'/speaking-level', [
                    'question' => $assessment->question,
                    'topic' => $assessment->topic,
                    'lang' => 'en',
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Speaking evaluator connection failed.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Speaking evaluator returned HTTP '.$response->status().'.');
        }

        $payload = $response->json();

        if (! is_array($payload) || ! is_string($payload['transcript'] ?? null) || ! is_numeric($payload['overall_score'] ?? null)) {
            throw new RuntimeException('Speaking evaluator returned an invalid response.');
        }

        return $payload;
    }
}
