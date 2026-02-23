<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->timestamp('scheduled_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->string('confirm_method', 20)->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['lead_id', 'status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
