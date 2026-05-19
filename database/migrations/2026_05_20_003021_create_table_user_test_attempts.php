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
        Schema::create('user_test_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('collection_test_id');
            $table->unsignedBigInteger('status')->default('1');
            $table->integer('correct_count');
            $table->integer('total_score');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('collection_test_id')->references('id')->on('collection_test')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_test_attempts');
    }
};
