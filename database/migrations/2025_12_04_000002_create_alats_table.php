<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alats', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('merk_type')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('kode_inventaris', 100)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('cabang_id')->constrained('cabangs')->restrictOnDelete();
            $table->string('lokasi')->nullable();
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->enum('status_kepemilikan', ['milik_sendiri', 'sewa', 'pinjam', 'leasing'])->default('milik_sendiri');
            $table->boolean('is_active')->default(true);

            // Review workflow
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('approval_note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('cabang_id');
            $table->index('kondisi');
            $table->index('status_kepemilikan');
            $table->index('review_status');
            $table->index('is_active');
            $table->index('kode_inventaris');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alats');
    }
};
