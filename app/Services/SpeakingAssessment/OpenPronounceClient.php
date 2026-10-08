<?php

namespace App\Services\SpeakingAssessment;

use App\Models\SpeakingAssessment\SpeakingAssessment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OpenPronounceClient implements PronunciationScorer
{
    /** @return array<string, mixed> */
    public function score(SpeakingAssessment $assessment): array
    {
        $disk = Storage::disk(config('services.speaking_assessment.storage_disk'));
        $stream = $disk->readStream($assessment->audio_path);

        if (! is_resource($stream)) {
            throw new RuntimeException('The speaking assessment audio file is unavailable.');
        }

        $audio = stream_get_contents($stream);
        fclose($stream);

        if (! is_string($audio) || $audio === '') {
            throw new RuntimeException('The speaking assessment audio file is empty.');
        }

        $baseUrl = rtrim((string) config('services.openpronounce.url'), '/');
        $lang = str_starts_with($assessment->dialect, 'fr-') ? 'fr'
            : (str_starts_with($assessment->dialect, 'es-') ? 'es' : 'en');

        try {
            $response = Http::connectTimeout(10)
                ->timeout((int) config('services.openpronounce.timeout', 600))
                ->attach('file', $audio, basename($assessment->audio_path))
                ->post($baseUrl.'/pronunciation', [
                    'expected_text' => $assessment->reference_text,
                    'lang' => $lang,
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('OpenPronounce connection failed.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('OpenPronounce returned HTTP '.$response->status().'.');
        }

        $payload = $response->json();

        if (! is_array($payload) || ! is_numeric($payload['score'] ?? null)) {
            throw new RuntimeException('OpenPronounce returned an invalid assessment response.');
        }

        return [
            'status' => 'success',
            'provider' => 'openpronounce',
            'version' => 'self-hosted',
            'text_score' => [
                'text' => $payload['transcribe'] ?? null,
                'score' => (float) $payload['score'],
                'differences' => $payload['differences'] ?? [],
                'acoustic_distance' => $payload['acoustic_distance'] ?? null,
                'prosody' => $payload['prosody'] ?? null,
                'raw' => $payload,
            ],
        ];
    }
}
