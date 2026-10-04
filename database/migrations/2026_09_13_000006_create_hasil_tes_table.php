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
        Schema::create('hasil_tes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_tes_id')->constrained('periode_tes')->restrictOnDelete();
            $table->foreignId('pegawai_id')->constrained('pegawai')->restrictOnDelete();
            $table->smallInteger('skor_listening');
            $table->smallInteger('skor_structure');
            $table->smallInteger('skor_reading');
            $table->smallInteger('skor_total');
            $table->date('tanggal_tes');
            $table->date('berlaku_sampai')->nullable();
            $table->string('no_sertifikat', 50)->nullable();
            $table->enum('sumber_input', ['impor', 'manual']);
            $table->foreignId('import_batch_id')->nullable()->constrained('import_batches')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['periode_tes_id', 'pegawai_id']);
            $table->index(['pegawai_id', 'tanggal_tes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_tes');
    }
};
