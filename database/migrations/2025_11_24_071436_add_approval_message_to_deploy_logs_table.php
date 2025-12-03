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
            $table->foreignId('approval_message_id')->nullable()->after('project_id')->constrained('approval_messages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deploy_logs', function (Blueprint $table) {
            $table->dropForeign(['approval_message_id']);
            $table->dropColumn('approval_message_id');
        });
    }
};
