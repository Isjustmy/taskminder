<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('student_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_submitted')->nullable();
            $table->boolean('is_late')->nullable();
            $table->tinyInteger('score')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->datetime('scored_at')->nullable();
            $table->text('feedback_content')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_tasks');
    }
};
