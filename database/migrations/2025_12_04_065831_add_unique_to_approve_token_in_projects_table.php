<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 注意: approve_tokenのユニーク制約は既にcreate_projects_tableで作成されている可能性があります。
     * このマイグレーションは、既存のデータベースに後から追加する場合のためのものです。
     */
    public function up(): void
    {
        // ユニークインデックスが既に存在する場合はスキップ
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->unique('approve_token');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // 1061は「Duplicate key name」エラーコード
            if (strpos($e->getMessage(), '1061') !== false || strpos($e->getMessage(), 'Duplicate key name') !== false) {
                // ユニークインデックスが既に存在する場合は無視
                return;
            }
            // その他のエラーは再スロー
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['approve_token']);
        });
    }
};
