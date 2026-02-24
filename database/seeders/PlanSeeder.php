<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'max_users' => 2,
                'max_projects' => 1,
                'max_channel_partners' => 10,
                'max_leads' => 200,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'max_users' => 10,
                'max_projects' => 5,
                'max_channel_partners' => 50,
                'max_leads' => 1000,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'max_users' => 50,
                'max_projects' => 20,
                'max_channel_partners' => 200,
                'max_leads' => 5000,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['is_active' => true])
            );
        }
    }
}
