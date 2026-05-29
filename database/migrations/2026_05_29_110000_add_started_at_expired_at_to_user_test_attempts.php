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
            $table->timestamp('started_at')->nullable()->after('total_time');
            $table->timestamp('expired_at')->nullable()->after('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_test_attempts', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'expired_at']);
        });
    }
};
