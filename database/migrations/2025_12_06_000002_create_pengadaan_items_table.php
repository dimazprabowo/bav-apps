<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengadaan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengadaan_id')->constrained('pengadaans')->cascadeOnDelete();
            $table->string('nama_item');
            $table->string('kategori_item')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->foreignId('satuan_id')->constrained('satuans')->restrictOnDelete();
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->index('pengadaan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengadaan_items');
    }
};
