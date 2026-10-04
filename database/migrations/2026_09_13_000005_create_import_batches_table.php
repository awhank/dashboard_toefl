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
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_tes_id')->constrained('periode_tes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('nama_file', 255);
            $table->integer('jumlah_baris')->default(0);
            $table->integer('jumlah_sukses')->default(0);
            $table->integer('jumlah_gagal')->default(0);
            $table->enum('status', ['selesai', 'dibatalkan'])->default('selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
