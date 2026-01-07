<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 注意: server_dirカラムは既にcreate_projects_tableで作成されている可能性があります。
     * このマイグレーションは、既存のデータベースに後から追加する場合のためのものです。
     */
    public function up(): void
    {
        // server_dirカラムが既に存在する場合はスキップ
        if (!Schema::hasColumn('projects', 'server_dir')) {
            Schema::table('projects', function (Blueprint $table) {
                // after()を削除して、MySQLのバージョン依存を避ける
                $table->string('server_dir')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('server_dir');
        });
    }
};
