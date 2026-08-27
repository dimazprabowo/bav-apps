<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_book_peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alat_id')->constrained('alats')->restrictOnDelete();
            $table->foreignId('peminjam_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('cabang_id')->constrained('cabangs')->restrictOnDelete();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali_rencana');
            $table->date('tanggal_kembali_aktual')->nullable();
            $table->enum('status', ['requested', 'approved', 'rejected', 'borrowed', 'returned', 'overdue', 'cancelled'])->default('requested');
            $table->enum('kondisi_pinjam', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->enum('kondisi_kembali', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable();
            $table->text('catatan')->nullable();

            // Approval workflow
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('alat_id');
            $table->index('peminjam_id');
            $table->index('cabang_id');
            $table->index('status');
            $table->index('tanggal_pinjam');
            $table->index('tanggal_kembali_rencana');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_book_peminjaman');
    }
};
