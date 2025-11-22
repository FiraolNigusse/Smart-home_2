<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Seed devices
        $this->call(DeviceSeeder::class);

        // Seed rules
        $this->call(RuleSeeder::class);

        // Create default owner user
        $ownerRole = Role::where('slug', 'owner')->first();
        User::updateOrCreate(
            ['email' => 'owner@smarthome.local'],
            [
                'name' => 'System Owner',
                'password' => bcrypt('password'),
                'role_id' => $ownerRole?->id,
            ]
        );

        // Create sample family member
        $familyRole = Role::where('slug', 'family')->first();
        User::updateOrCreate(
            ['email' => 'family@smarthome.local'],
            [
                'name' => 'Family Member',
                'password' => bcrypt('password'),
                'role_id' => $familyRole?->id,
            ]
        );

        // Create sample guest
        $guestRole = Role::where('slug', 'guest')->first();
        User::updateOrCreate(
            ['email' => 'guest@smarthome.local'],
            [
                'name' => 'Guest User',
                'password' => bcrypt('password'),
                'role_id' => $guestRole?->id,
            ]
        );
    }
}
