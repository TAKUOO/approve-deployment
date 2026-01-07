<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * approval_messagesテーブルが作成された後に、外部キー制約を追加します。
     */
    public function up(): void
    {
        Schema::table('deploy_logs', function (Blueprint $table) {
            // approval_message_idカラムが存在し、approval_messagesテーブルが存在する場合のみ外部キーを追加
            if (Schema::hasColumn('deploy_logs', 'approval_message_id') && Schema::hasTable('approval_messages')) {
                $table->foreign('approval_message_id')
                    ->references('id')
                    ->on('approval_messages')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deploy_logs', function (Blueprint $table) {
            // 外部キー制約を削除
            if (Schema::hasColumn('deploy_logs', 'approval_message_id')) {
                try {
                    $table->dropForeign(['approval_message_id']);
                } catch (\Exception $e) {
                    // 外部キーが存在しない場合は無視
                }
            }
        });
    }
};
