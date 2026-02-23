<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cp_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_partner_id')->constrained('channel_partners')->cascadeOnDelete();
            $table->foreignId('builder_firm_id')->constrained('builder_firms')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['channel_partner_id', 'builder_firm_id']);
            $table->index(['builder_firm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cp_applications');
    }
};
