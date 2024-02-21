<?php

// Task migration

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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 75);
            $table->text('description');
            $table->string('file_path')->nullable();
            $table->string('link')->nullable();
            $table->foreignId('class_id')->nullable()->references('id')->on('student_classes')->cascadeOnDelete();
            $table->foreignId('creator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('mata_pelajaran', ['RPL - Produktif', 'Animasi - Produktif', 'Broadcasting - Produktif', 'TO - Produktif', 'TPFL - Produktif', 'Matematika', 'Sejarah', 'Pendidikan Agama', 'IPAS', 'Olahraga', 'Bahasa Indonesia', 'Bahasa Sunda', 'Bahasa Inggris', 'Bahasa Jepang']);
            $table->datetime('deadline');
            $table->datetime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
