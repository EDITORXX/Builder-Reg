<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('builder_firm_id')->constrained('builder_firms')->cascadeOnDelete();
            $table->string('name');
            $table->string('location');
            $table->unsignedInteger('lock_days_override')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['builder_firm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
