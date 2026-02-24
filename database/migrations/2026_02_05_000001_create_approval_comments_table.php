<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_message_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name', 120);
            $table->text('body');
            $table->decimal('x_ratio', 8, 5);
            $table->decimal('y_ratio', 8, 5);
            $table->json('mentions')->nullable();
            $table->timestamps();

            $table->index(['approval_message_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_comments');
    }
};
