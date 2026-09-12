<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengadaan_id')->constrained('pengadaans')->cascadeOnDelete();
            $table->string('no_invoice');
            $table->date('tanggal_invoice');
            $table->decimal('jumlah', 15, 2);
            $table->date('jatuh_tempo')->nullable();
            $table->text('catatan')->nullable();

            // Dokumen invoice (async via worker)
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_status')->nullable()->comment('null=no file, processing, completed, failed');
            $table->string('file_error')->nullable();
            $table->timestamp('file_processed_at')->nullable();

            $table->timestamps();

            $table->index('pengadaan_id');
            $table->index('tanggal_invoice');
            $table->index('jatuh_tempo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
