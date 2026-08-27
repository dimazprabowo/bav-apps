<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('cabang_id')->nullable()->after('id')->constrained('cabangs')->nullOnDelete();
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('position')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('position');

            $table->index('cabang_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
            $table->dropColumn(['cabang_id', 'phone', 'position', 'is_active']);
        });
    }
};
