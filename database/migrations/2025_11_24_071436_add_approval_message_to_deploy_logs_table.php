<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 注意: 外部キー制約は別のマイグレーションで追加します。
     * これは、approval_messagesテーブルが先に作成される必要があるためです。
     */
    public function up(): void
    {
        // カラムが存在しない場合のみ追加
        if (!Schema::hasColumn('deploy_logs', 'approval_message_id')) {
            Schema::table('deploy_logs', function (Blueprint $table) {
                // カラムだけ追加（外部キー制約なし）
                // after()を削除して、MySQLのバージョン依存を避ける
                $table->unsignedBigInteger('approval_message_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deploy_logs', function (Blueprint $table) {
            // 外部キーが存在する場合は削除（後続のマイグレーションで追加される可能性があるため）
            if (Schema::hasColumn('deploy_logs', 'approval_message_id')) {
                // 外部キー制約を削除（存在する場合）
                try {
                    $table->dropForeign(['approval_message_id']);
                } catch (\Exception $e) {
                    // 外部キーが存在しない場合は無視
                }
                $table->dropColumn('approval_message_id');
            }
        });
    }
};
