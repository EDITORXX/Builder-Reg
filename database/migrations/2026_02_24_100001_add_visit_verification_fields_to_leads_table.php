<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('visit_photo_path')->nullable()->after('notes');
            $table->text('verification_reject_reason')->nullable()->after('visit_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['visit_photo_path', 'verification_reject_reason']);
        });
    }
};
