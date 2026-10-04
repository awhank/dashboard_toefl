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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pegawai', 30)->unique();
            $table->enum('jenis_nomor', ['nip', 'nipppk', 'internal']);
            $table->string('nama', 150);
            $table->enum('jenis_pegawai', ['dosen', 'tendik']);
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->date('tanggal_nonaktif')->nullable();
            $table->string('keterangan_nonaktif', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
