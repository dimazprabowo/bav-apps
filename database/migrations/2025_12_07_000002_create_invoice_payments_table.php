<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->string('metode_bayar')->nullable();
            $table->enum('status_approval', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('catatan')->nullable();

            // Bukti transfer (async via worker)
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_status')->nullable()->comment('null=no file, processing, completed, failed');
            $table->string('file_error')->nullable();
            $table->timestamp('file_processed_at')->nullable();

            $table->timestamps();

            $table->index('invoice_id');
            $table->index('status_approval');
            $table->index('tanggal_bayar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
