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
        Schema::create('user_test_answer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_test_attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->string('user_answer');
            $table->unsignedBigInteger('is_correct');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_test_answer');
    }
};
