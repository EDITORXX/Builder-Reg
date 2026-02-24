<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('builder_firm_id')->constrained('builder_firms')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('channel_partner_id')->constrained('channel_partners')->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_mobile', 20);
            $table->string('customer_email')->nullable();
            $table->timestamp('scheduled_at');
            $table->string('token', 64)->unique();
            $table->string('status', 20)->default('scheduled');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->index(['token']);
            $table->index(['channel_partner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_schedules');
    }
};
