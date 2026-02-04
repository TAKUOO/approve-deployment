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
        Schema::table('deploy_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('deploy_logs', 'access_token')) {
                $table->string('access_token')->nullable()->index()->after('github_run_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deploy_logs', function (Blueprint $table) {
            if (Schema::hasColumn('deploy_logs', 'access_token')) {
                $table->dropIndex(['access_token']);
                $table->dropColumn('access_token');
            }
        });
    }
};

