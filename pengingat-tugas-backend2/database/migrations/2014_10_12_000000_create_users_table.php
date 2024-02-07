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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('nomor_absen')->unique()->nullable();
            $table->string('name', 80);
            $table->string('email', 50)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone_number', 15)->unique();
            $table->foreignId('student_class_id')->nullable()->references('id')->on('student_classes')->cascadeOnDelete();
            $table->enum('guru_mata_pelajaran', ['RPL - Produktif', 'Animasi - Produktif', 'Broadcasting - Produktif', 'TO - Produktif', 'TPFL - Produktif', 'Matematika', 'Sejarah', 'Pendidikan Agama', 'IPAS', 'Olahraga', 'Bahasa Indonesia', 'Bahasa Sunda', 'Bahasa Inggris', 'Bahasa Jepang'])->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
