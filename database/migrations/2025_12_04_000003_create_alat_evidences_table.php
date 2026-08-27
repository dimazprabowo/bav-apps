<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alat_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alat_id')->constrained('alats')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_status')->default('pending')->comment('pending, processing, completed, failed');
            $table->string('file_error')->nullable();
            $table->timestamp('file_processed_at')->nullable();
            $table->timestamps();

            $table->index('alat_id');
            $table->index('file_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alat_evidence');
    }
};
