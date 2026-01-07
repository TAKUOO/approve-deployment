<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 注意: approve_token_expires_atカラムは既にcreate_projects_tableで作成されている可能性があります。
     * このマイグレーションは、既存のデータベースに後から追加する場合のためのものです。
     */
    public function up(): void
    {
        // approve_token_expires_atカラムが存在しない場合のみ追加
        if (!Schema::hasColumn('projects', 'approve_token_expires_at')) {
            Schema::table('projects', function (Blueprint $table) {
                // after()を削除して、MySQLのバージョン依存を避ける
                $table->timestamp('approve_token_expires_at')->nullable();
            });
        }
        
        // approve_tokenのインデックスが存在しない場合のみ追加
        // ユニークインデックスが既に存在する可能性があるため、try-catchで処理
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->index('approve_token'); // 検索速度向上
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // 1061は「Duplicate key name」エラーコード
            // インデックスが既に存在する場合は無視
            if (strpos($e->getMessage(), '1061') === false && strpos($e->getMessage(), 'Duplicate key name') === false) {
                // その他のエラーは再スロー
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['approve_token']);
            $table->dropColumn('approve_token_expires_at');
        });
    }
};
