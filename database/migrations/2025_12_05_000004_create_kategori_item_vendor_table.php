<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_item_vendor', function (Blueprint $table) {
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kategori_item_id')->constrained()->cascadeOnDelete();
            $table->primary(['vendor_id', 'kategori_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_item_vendor');
    }
};
