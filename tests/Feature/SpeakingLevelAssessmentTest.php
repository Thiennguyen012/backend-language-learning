<?php

namespace Tests\Feature;

use App\Jobs\EvaluateSpeakingLevelAnswer;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SpeakingLevelAssessmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->boolean('is_super_admin')->default(false);
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('speaking_level_assessments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id');
            $table->string('topic');
            $table->text('question');
            $table->string('dialect')->default('en-us');
            $table->unsignedSmallInteger('preparation_seconds')->default(15);
            $table->unsignedSmallInteger('response_seconds')->default(90);
            $table->string('status')->default('awaiting_answer');
            $table->string('audio_path')->nullable();
            $table->text('transcript')->nullable();
            $table->json('result')->nullable();
            $table->string('error_code')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Sanctum::actingAs(User::factory()->create([
            'is_super_admin' => true,
            'status' => 1,
        ]));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('speaking_level_assessments');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_it_lists_available_speaking_topics(): void
    {
        $this->getJson('/api/admin/speaking-level-assessments/topics')
            ->assertOk()
            ->assertJsonPath('status_code', 200)
            ->assertJsonStructure(['data' => [['id', 'label']]]);
    }

    public function test_it_creates_an_assessment_question_for_a_topic(): void
    {
        $this->postJson('/api/admin/speaking-level-assessments', ['topic' => 'technology'])
            ->assertCreated()
            ->assertJsonPath('data.topic', 'technology')
            ->assertJsonPath('data.status', 'awaiting_answer')
            ->assertJsonStructure(['data' => ['id', 'question', 'preparation_seconds', 'response_seconds']]);

        $this->assertDatabaseHas('speaking_level_assessments', [
            'topic' => 'technology',
            'status' => 'awaiting_answer',
        ]);
    }

    public function test_it_stores_audio_and_dispatches_evaluation(): void
    {
        Storage::fake('local');
        Queue::fake();

        $created = $this->postJson('/api/admin/speaking-level-assessments', [
            'topic' => 'daily-life',
        ])->json('data');

        $this->post('/api/admin/speaking-level-assessments/'.$created['id'].'/answer', [
            'audio' => UploadedFile::fake()->createWithContent('answer.m4a', 'audio-content'),
        ], ['Accept' => 'application/json'])
            ->assertAccepted()
            ->assertJsonPath('data.status', 'queued');

        Queue::assertPushed(EvaluateSpeakingLevelAnswer::class);
    }
}
