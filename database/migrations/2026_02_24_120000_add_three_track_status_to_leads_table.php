<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('visit_status', 30)->nullable()->after('status');
            $table->string('verification_status', 30)->nullable()->after('visit_status');
            $table->string('sales_status', 30)->default('new')->after('verification_status');
            $table->timestamp('last_verified_visit_at')->nullable()->after('sales_status');
        });

        $this->backfillFromStatus();
    }

    private function backfillFromStatus(): void
    {
        $mapVisit = [
            'visit_scheduled' => 'visit_scheduled',
            'visit_done' => 'visited',
            'verified_visit' => 'visited',
            'pending_verification' => 'visited',
            'rejected' => 'visited',
        ];
        $mapVerification = [
            'pending_verification' => 'pending_verification',
            'verified_visit' => 'verified_visit',
            'rejected' => 'rejected_verification',
        ];
        $mapSales = [
            'new' => 'new',
            'contacted' => 'new',
            'visit_scheduled' => 'new',
            'visit_done' => 'new',
            'negotiation' => 'negotiation',
            'booked' => 'booked',
            'lost' => 'lost',
            'pending_verification' => 'new',
            'verified_visit' => 'new',
            'rejected' => 'new',
        ];

        $leads = DB::table('leads')->get();
        foreach ($leads as $lead) {
            $status = $lead->status ?? 'new';
            $visitStatus = $mapVisit[$status] ?? null;
            $verificationStatus = $mapVerification[$status] ?? null;
            $salesStatus = $mapSales[$status] ?? 'new';
            $lastVerified = ($status === 'verified_visit') ? ($lead->updated_at ?? null) : null;

            DB::table('leads')->where('id', $lead->id)->update([
                'visit_status' => $visitStatus,
                'verification_status' => $verificationStatus,
                'sales_status' => $salesStatus,
                'last_verified_visit_at' => $lastVerified,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['visit_status', 'verification_status', 'sales_status', 'last_verified_visit_at']);
        });
    }
};
