<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('user_test_answers');

        Schema::create('user_test_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_test_attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->text('user_answer');
            $table->tinyInteger('is_correct')->default(0);
            $table->timestamps();

            $table->foreign('user_test_attempt_id')
                ->references('id')
                ->on('user_test_attempts')
                ->onDelete('cascade');

            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_test_answers');
    }
};
