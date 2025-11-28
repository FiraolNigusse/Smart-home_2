<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SensitivityLevel;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Owner',
                'slug' => 'owner',
                'description' => 'Full system access and control',
                'hierarchy' => 3,
            ],
            [
                'name' => 'Family Member',
                'slug' => 'family',
                'description' => 'Partial access to devices and features',
                'hierarchy' => 2,
            ],
            [
                'name' => 'Guest',
                'slug' => 'guest',
                'description' => 'Limited, time-restricted access',
                'hierarchy' => 1,
            ],
        ];

        foreach ($roles as $role) {
            $levelSlug = match ($role['slug']) {
                'owner' => 'confidential',
                'family' => 'internal',
                default => 'public',
            };

            $sensitivityLevel = SensitivityLevel::where('slug', $levelSlug)->first();

            $role['sensitivity_level_id'] = $sensitivityLevel?->id;

            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
