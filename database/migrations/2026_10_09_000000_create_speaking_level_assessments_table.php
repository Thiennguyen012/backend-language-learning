<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('speaking_level_assessments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic', 50);
            $table->text('question');
            $table->string('dialect', 10)->default('en-us');
            $table->unsignedSmallInteger('preparation_seconds')->default(15);
            $table->unsignedSmallInteger('response_seconds')->default(90);
            $table->string('status', 30)->default('awaiting_answer');
            $table->string('audio_path')->nullable();
            $table->text('transcript')->nullable();
            $table->json('result')->nullable();
            $table->string('error_code', 80)->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('speaking_level_assessments');
    }
};
