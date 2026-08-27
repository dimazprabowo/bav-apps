<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alat_kalibrasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alat_id')->constrained('alats')->cascadeOnDelete();
            $table->date('tanggal_kalibrasi');
            $table->date('tanggal_kalibrasi_berikutnya')->nullable();
            $table->string('vendor')->nullable();
            $table->string('sertifikat_no')->nullable();
            $table->enum('hasil', ['lulus', 'gagal', 'perlu_perbaikan'])->default('lulus');
            $table->text('catatan')->nullable();

            // Sertifikat file (async via worker)
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_status')->nullable()->comment('null=no file, processing, completed, failed');
            $table->string('file_error')->nullable();
            $table->timestamp('file_processed_at')->nullable();

            $table->timestamps();

            $table->index('alat_id');
            $table->index('tanggal_kalibrasi');
            $table->index('tanggal_kalibrasi_berikutnya');
            $table->index('hasil');
            $table->index('file_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alat_kalibrasis');
    }
};
