<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('builder_firm_id')->constrained('builder_firms')->cascadeOnDelete();
            $table->json('data');
            $table->string('submissible_type', 50)->nullable();
            $table->unsignedBigInteger('submissible_id')->nullable();
            $table->timestamps();
            $table->index(['form_id', 'created_at']);
            $table->index(['builder_firm_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
