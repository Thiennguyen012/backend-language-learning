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
        Schema::table('user_test_attempts', function (Blueprint $table) {
            $table->text('question_ids')->nullable()->after('total_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_test_attempts', function (Blueprint $table) {
            $table->dropColumn('question_ids');
        });
    }
};
