<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `user_test_attempts` MODIFY `started_time` DATETIME NULL");
        DB::statement("ALTER TABLE `user_test_attempts` MODIFY `finished_time` DATETIME NULL");

        Schema::table('user_test_attempts', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_test_attempts', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('total_time');
        });

        DB::statement("ALTER TABLE `user_test_attempts` MODIFY `started_time` TIME NULL");
        DB::statement("ALTER TABLE `user_test_attempts` MODIFY `finished_time` TIME NULL");
    }
};
