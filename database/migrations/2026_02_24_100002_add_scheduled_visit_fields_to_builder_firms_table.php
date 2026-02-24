<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('builder_firms', function (Blueprint $table) {
            $table->boolean('scheduled_visit_enabled')->default(false)->after('is_active');
            $table->unsignedInteger('scheduled_visit_limit')->nullable()->after('scheduled_visit_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('builder_firms', function (Blueprint $table) {
            $table->dropColumn(['scheduled_visit_enabled', 'scheduled_visit_limit']);
        });
    }
};
