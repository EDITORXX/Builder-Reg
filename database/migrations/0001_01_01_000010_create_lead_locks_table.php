<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('customer_mobile', 20);
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('channel_partner_id')->nullable()->constrained('channel_partners')->nullOnDelete();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->string('status', 20)->default('active');
            $table->foreignId('unlocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('unlock_reason')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'customer_mobile', 'status']);
            $table->index(['end_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_locks');
    }
};
