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
        Schema::create('periode_tes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_tes_id')->constrained('jenis_tes')->restrictOnDelete();
            $table->smallInteger('tahun');
            $table->tinyInteger('gelombang');
            $table->string('nama', 100);
            $table->date('tanggal_pelaksanaan');
            $table->string('penyelenggara', 150)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['jenis_tes_id', 'tahun', 'gelombang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_tes');
    }
};
