<?php

namespace Tests\Feature;

use App\Models\SpeakingAssessment\SpeakingLevelAssessment;
use App\Services\SpeakingAssessment\OpenSpeakingLevelEvaluatorClient;
use App\Services\SpeakingAssessment\SpeakingLevelEvaluator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OpenSpeakingLevelEvaluatorClientTest extends TestCase
{
    public function test_it_sends_the_open_answer_and_returns_the_rubric(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('answer.m4a', 'audio-bytes');
        config([
            'services.speaking_assessment.storage_disk' => 'local',
            'services.openpronounce.url' => 'http://127.0.0.1:8001',
            'services.openpronounce.timeout' => 120,
        ]);

        Http::fake([
            'http://127.0.0.1:8001/speaking-level' => Http::response([
                'transcript' => 'Technology helps people learn more quickly.',
                'overall_score' => 72.5,
                'cefr_level' => 'B2',
                'scores' => [
                    'pronunciation' => 75,
                    'fluency' => 70,
                    'grammar' => 80,
                    'vocabulary' => 70,
                    'coherence' => 68,
                    'topic_relevance' => 85,
                ],
                'grammar_errors' => [],
                'strengths' => ['Clear main idea'],
                'improvements' => ['Add an example'],
                'upgraded_answer' => 'Technology enables people to learn more efficiently.',
            ]),
        ]);

        $assessment = new SpeakingLevelAssessment([
            'audio_path' => 'answer.m4a',
            'question' => 'How has technology changed learning?',
            'topic' => 'technology',
        ]);

        $result = app(SpeakingLevelEvaluator::class)->evaluate($assessment);

        $this->assertInstanceOf(OpenSpeakingLevelEvaluatorClient::class, app(SpeakingLevelEvaluator::class));
        $this->assertSame('Technology helps people learn more quickly.', $result['transcript']);
        $this->assertSame(72.5, $result['overall_score']);
        $this->assertSame('B2', $result['cefr_level']);
        Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:8001/speaking-level'
            && str_contains($request->body(), 'name="question"')
            && str_contains($request->body(), 'How has technology changed learning?')
            && str_contains($request->body(), 'name="topic"')
            && str_contains($request->body(), 'technology'));
    }
}
