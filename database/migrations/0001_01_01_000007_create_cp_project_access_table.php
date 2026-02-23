<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cp_project_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_partner_id')->constrained('channel_partners')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('status', 20)->default('allowed');
            $table->timestamps();
            $table->unique(['channel_partner_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cp_project_access');
    }
};
