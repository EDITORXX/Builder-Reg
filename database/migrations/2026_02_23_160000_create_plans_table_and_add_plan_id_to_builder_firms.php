<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('max_users')->default(2);
            $table->unsignedInteger('max_projects')->default(1);
            $table->unsignedInteger('max_channel_partners')->default(10);
            $table->unsignedInteger('max_leads')->default(200);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('builder_firms', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('is_active')->constrained('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('builder_firms', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
        });
        Schema::dropIfExists('plans');
    }
};
