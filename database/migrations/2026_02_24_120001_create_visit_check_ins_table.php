<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('visit_schedule_id')->nullable()->constrained('visit_schedules')->nullOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('channel_partner_id')->constrained('channel_partners')->cascadeOnDelete();
            $table->string('customer_mobile', 20);
            $table->timestamp('submitted_at');
            $table->string('visit_photo_path')->nullable();
            $table->string('verification_status', 30)->default('pending_verification');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->string('visit_type', 30); // scheduled_checkin | direct
            $table->timestamps();
            $table->index(['lead_id']);
            $table->index(['project_id', 'customer_mobile']);
            $table->index(['verification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_check_ins');
    }
};
