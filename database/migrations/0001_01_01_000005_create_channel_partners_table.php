<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('firm_name')->nullable();
            $table->string('rera_number', 100)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_partners');
    }
};
