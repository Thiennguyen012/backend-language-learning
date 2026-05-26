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
            $table->time('started_time')->nullable()->after('total_score');
            $table->time('finished_time')->nullable()->after('started_time');
            $table->time('total_time')->nullable()->after('finished_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_test_attempts', function (Blueprint $table) {
            $table->dropColumn(['started_time', 'finished_time', 'total_time']);
        });
    }
};
