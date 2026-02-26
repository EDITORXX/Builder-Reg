<?php

namespace Database\Seeders;

use App\Models\BuilderFirm;
use App\Models\ChannelPartner;
use App\Models\CpApplication;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EldecoSampleSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::first();
        if (! $plan) {
            $this->call(PlanSeeder::class);
            $plan = Plan::first();
        }

        $builder = BuilderFirm::where('name', 'Eldeco Group')->first();
        if (! $builder) {
            $builder = BuilderFirm::create([
                'name' => 'Eldeco Group',
                'slug' => 'eldeco-group-local',
                'address' => 'Eldeco Office, Noida',
                'default_lock_days' => 30,
                'settings' => ['confirm_methods' => ['otp', 'manual'], 'lock_privacy' => true, 'allow_direct_walkin' => true],
                'is_active' => true,
                'plan_id' => $plan?->id,
            ]);
        } else {
            // If multiple "Eldeco Group" exist, prefer the one that has no leads (e.g. tenant eldeco-group-7b6e)
            $builderWithNoLeads = BuilderFirm::where('name', 'Eldeco Group')
                ->get()
                ->first(fn ($b) => ! Lead::whereHas('project', fn ($q) => $q->where('builder_firm_id', $b->id))->exists());
            if ($builderWithNoLeads) {
                $builder = $builderWithNoLeads;
            }
        }

        $avnish = User::firstOrCreate(
            ['email' => 'avnish@eldeco.com'],
            [
                'name' => 'Avnish',
                'password' => Hash::make('password'),
                'role' => User::ROLE_MANAGER,
                'builder_firm_id' => $builder->id,
                'is_active' => true,
            ]
        );
        if ($avnish->builder_firm_id !== $builder->id) {
            $avnish->update(['builder_firm_id' => $builder->id]);
        }

        $project = Project::firstOrCreate(
            [
                'builder_firm_id' => $builder->id,
                'name' => 'Eldeco Greens',
            ],
            [
                'location' => null,
                'status' => 'active',
            ]
        );

        $hasLeads = Lead::whereHas('project', fn ($q) => $q->where('builder_firm_id', $builder->id))->exists();
        if ($hasLeads) {
            return;
        }

        $cp1User = User::firstOrCreate(
            ['email' => 'cp1@eldeco-sample.com'],
            [
                'name' => 'Company A User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CHANNEL_PARTNER,
                'builder_firm_id' => null,
                'is_active' => true,
            ]
        );

        $cp2User = User::firstOrCreate(
            ['email' => 'cp2@eldeco-sample.com'],
            [
                'name' => 'Company B User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CHANNEL_PARTNER,
                'builder_firm_id' => null,
                'is_active' => true,
            ]
        );

        $cp1 = ChannelPartner::firstOrCreate(
            ['user_id' => $cp1User->id],
            ['firm_name' => 'Company A']
        );

        $cp2 = ChannelPartner::firstOrCreate(
            ['user_id' => $cp2User->id],
            ['firm_name' => 'Company B']
        );

        CpApplication::firstOrCreate(
            ['channel_partner_id' => $cp1->id, 'builder_firm_id' => $builder->id],
            [
                'status' => CpApplication::STATUS_APPROVED,
                'manager_id' => $avnish->id,
                'reviewed_at' => now(),
            ]
        );

        CpApplication::firstOrCreate(
            ['channel_partner_id' => $cp2->id, 'builder_firm_id' => $builder->id],
            [
                'status' => CpApplication::STATUS_APPROVED,
                'manager_id' => $avnish->id,
                'reviewed_at' => now(),
            ]
        );

        $customers = [];
        for ($i = 1; $i <= 10; $i++) {
            $customers[] = Customer::create([
                'name' => "Sample Customer {$i}",
                'mobile' => '98765' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'email' => "sample{$i}@example.com",
                'city' => 'Noida',
            ]);
        }

        $salesStatuses = [
            Lead::SALES_NEW,
            Lead::SALES_NEW,
            Lead::SALES_NEGOTIATION,
            Lead::SALES_NEGOTIATION,
            Lead::SALES_HOLD,
            Lead::SALES_HOLD,
            Lead::SALES_BOOKED,
            Lead::SALES_BOOKED,
            Lead::SALES_LOST,
            Lead::SALES_LOST,
        ];

        $visitVerificationBySales = [
            Lead::SALES_NEW => [Lead::VISIT_SCHEDULED, null],
            Lead::SALES_NEGOTIATION => [Lead::VISITED, Lead::VERIFIED_VISIT],
            Lead::SALES_HOLD => [Lead::VISITED, Lead::VERIFIED_VISIT],
            Lead::SALES_BOOKED => [Lead::VISITED, Lead::VERIFIED_VISIT],
            Lead::SALES_LOST => [Lead::VISITED, Lead::VERIFIED_VISIT],
        ];

        $cps = [$cp1, $cp2];
        foreach ($salesStatuses as $index => $salesStatus) {
            $cp = $cps[$index % 2];
            [$visitStatus, $verificationStatus] = $visitVerificationBySales[$salesStatus];
            Lead::create([
                'project_id' => $project->id,
                'customer_id' => $customers[$index]->id,
                'channel_partner_id' => $cp->id,
                'created_by' => $cp->user_id,
                'status' => 'new',
                'source' => Lead::SOURCE_CHANNEL_PARTNER,
                'sales_status' => $salesStatus,
                'visit_status' => $visitStatus,
                'verification_status' => $verificationStatus,
                'last_verified_visit_at' => $verificationStatus === Lead::VERIFIED_VISIT ? now() : null,
            ]);
        }
    }
}
