<?php

namespace Database\Seeders;

use App\Models\BuilderFirm;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlanSeeder::class);

        // Localhost sample data: php artisan db:seed --class=EldecoSampleSeeder

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@builder.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'builder_firm_id' => null,
            'is_active' => true,
        ]);

        $basicPlan = Plan::where('slug', 'basic')->first();
        $builderFirm = BuilderFirm::create([
            'name' => 'Sample Builder Pvt Ltd',
            'slug' => 'sample-builder-' . substr(uniqid(), -4),
            'address' => '123 Main Street, City',
            'default_lock_days' => 30,
            'settings' => ['confirm_methods' => ['otp', 'manual'], 'lock_privacy' => true, 'allow_direct_walkin' => true],
            'is_active' => true,
            'plan_id' => $basicPlan?->id,
        ]);

        User::create([
            'name' => 'Builder Admin',
            'email' => 'admin@builder.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_BUILDER_ADMIN,
            'builder_firm_id' => $builderFirm->id,
            'is_active' => true,
        ]);
    }
}
