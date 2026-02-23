<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 20);
            $table->string('email')->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamps();
            $table->index('mobile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
