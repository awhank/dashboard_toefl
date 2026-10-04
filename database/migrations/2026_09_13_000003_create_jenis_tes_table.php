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
        Schema::create('jenis_tes', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->boolean('is_resmi');
            $table->smallInteger('skor_bagian_min');
            $table->smallInteger('skor_bagian_max');
            $table->smallInteger('skor_total_min');
            $table->smallInteger('skor_total_max');
            $table->tinyInteger('masa_berlaku_bulan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_tes');
    }
};
