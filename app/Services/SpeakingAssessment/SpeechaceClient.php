<?php

namespace App\Services\SpeakingAssessment;

use App\Models\SpeakingAssessment\SpeakingAssessment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SpeechaceClient implements PronunciationScorer
{
    /**
     * @throws ConnectionException
     */
    public function score(SpeakingAssessment $assessment): array
    {
        $disk = Storage::disk(config('services.speechace.storage_disk'));
        $stream = $disk->readStream($assessment->audio_path);

        if (! is_resource($stream)) {
            throw new RuntimeException('The speaking assessment audio file is unavailable.');
        }

        $audio = stream_get_contents($stream);
        fclose($stream);

        if (! is_string($audio) || $audio === '') {
            throw new RuntimeException('The speaking assessment audio file is empty.');
        }

        $baseUrl = rtrim((string) config('services.speechace.base_url'), '/');
        $query = http_build_query([
            'key' => config('services.speechace.api_key'),
            'dialect' => $assessment->dialect,
            'user_id' => hash_hmac('sha256', (string) $assessment->user_id, (string) config('app.key')),
        ]);
        $url = $baseUrl.'/api/scoring/text/v9/json?'.$query;

        $body = [
            'text' => $assessment->reference_text,
            'question_info' => $assessment->uuid,
            'no_mc' => '1',
        ];

        if (config('services.speechace.include_fluency')) {
            $body['include_fluency'] = '1';
        }

        try {
            $response = Http::connectTimeout(10)
                ->timeout(40)
                ->asMultipart()
                ->attach('user_audio_file', $audio, basename($assessment->audio_path))
                ->post($url, $body);
        } catch (ConnectionException) {
            throw new RuntimeException('Speechace connection failed.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Speechace returned HTTP '.$response->status().'.');
        }

        $payload = $response->json();

        if (! is_array($payload) || ($payload['status'] ?? null) !== 'success' || ! is_array($payload['text_score'] ?? null)) {
            throw new RuntimeException('Speechace returned an invalid assessment response.');
        }

        return $payload;
    }
}
