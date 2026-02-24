<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('builder_firm_id')->constrained('builder_firms')->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 30); // cp_registration, customer_registration
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index(['builder_firm_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
