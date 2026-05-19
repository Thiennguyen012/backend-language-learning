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
        Schema::create('collection_test', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_type_id')->nullable();
            $table->unsignedBigInteger('collection_id')->nullable();
            $table->string('test_name', 255);
            $table->integer('total_questions')->nullable();
            $table->integer('status')->default('1');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // References
            $table->foreign('test_type_id')->references('id')->on('test_type')->onDelete('set null');
            $table->foreign('collection_id')->references('id')->on('flashcard_collection')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_test');
    }
};
