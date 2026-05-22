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
        Schema::table('flashcard', function (Blueprint $table) {
            $table->unsignedBigInteger('word_type_id')->nullable()->after('translated_word');
            $table->foreign('word_type_id')
                ->references('id')
                ->on('word_type')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flashcard', function (Blueprint $table) {
            $table->dropForeign(['word_type_id']);
            $table->dropColumn('word_type_id');
        });
    }
};
