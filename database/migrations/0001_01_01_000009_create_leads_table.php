<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('channel_partner_id')->nullable()->constrained('channel_partners')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('new');
            $table->string('source', 30)->default('channel_partner');
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('property_type', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['project_id', 'channel_partner_id', 'status', 'created_at']);
            $table->index(['customer_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
