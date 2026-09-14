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

            // Approval pendaftaran user
            $table->string('approval_status', 20)->default('approved')->after('is_active');
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_by');

            $table->index('cabang_id');
            $table->index('is_active');
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropIndex(['cabang_id']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['approval_status']);
            $table->dropColumn([
                'cabang_id', 'phone', 'position', 'is_active',
                'approval_status', 'approved_at', 'approved_by',
                'rejected_at', 'rejected_by', 'rejection_reason',
            ]);
        });
    }
};
