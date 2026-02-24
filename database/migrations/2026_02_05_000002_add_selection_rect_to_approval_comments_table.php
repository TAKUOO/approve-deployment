<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_comments', function (Blueprint $table) {
            // Optional selection rectangle (top-left anchored by x_ratio/y_ratio)
            $table->decimal('w_ratio', 8, 5)->nullable()->after('y_ratio');
            $table->decimal('h_ratio', 8, 5)->nullable()->after('w_ratio');
        });
    }

    public function down(): void
    {
        Schema::table('approval_comments', function (Blueprint $table) {
            $table->dropColumn(['w_ratio', 'h_ratio']);
        });
    }
};

