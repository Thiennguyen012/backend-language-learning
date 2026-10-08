<?php

namespace Tests\Feature;

use App\Models\SpeakingAssessment\SpeakingAssessment;
use App\Services\SpeakingAssessment\OpenPronounceClient;
use App\Services\SpeakingAssessment\PronunciationScorer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OpenPronounceClientTest extends TestCase
{
    public function test_it_sends_audio_to_openpronounce_and_normalizes_the_result(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('sample.wav', 'audio-bytes');
        config([
            'services.speaking_assessment.provider' => 'openpronounce',
            'services.speaking_assessment.storage_disk' => 'local',
            'services.openpronounce.url' => 'http://127.0.0.1:8001',
            'services.openpronounce.timeout' => 120,
        ]);

        Http::fake([
            'http://127.0.0.1:8001/pronunciation' => Http::response([
                'score' => 87.5,
                'transcribe' => 'Hello there',
                'differences' => ['errors' => []],
            ]),
        ]);

        $assessment = new SpeakingAssessment([
            'audio_path' => 'sample.wav',
            'reference_text' => 'Hello there',
            'dialect' => 'en-us',
        ]);

        $result = app(OpenPronounceClient::class)->score($assessment);

        $this->assertInstanceOf(OpenPronounceClient::class, app(PronunciationScorer::class));
        $this->assertSame('success', $result['status']);
        $this->assertSame('Hello there', $result['text_score']['text']);
        $this->assertSame(87.5, $result['text_score']['score']);
        $this->assertSame('openpronounce', $result['provider']);
        Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:8001/pronunciation'
            && str_contains($request->body(), 'name="expected_text"')
            && str_contains($request->body(), 'Hello there')
            && str_contains($request->body(), 'name="lang"')
            && str_contains($request->body(), 'en'));
    }
}
