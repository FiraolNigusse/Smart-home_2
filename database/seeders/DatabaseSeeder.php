<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\UserAttribute;
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
        // Seed sensitivity levels first
        $this->call(SensitivityLevelSeeder::class);

        // Seed roles
        $this->call(RoleSeeder::class);

        // Seed devices
        $this->call(DeviceSeeder::class);

        // Seed rules
        $this->call(RuleSeeder::class);

        // Create default owner user
        $ownerRole = Role::where('slug', 'owner')->first();
        $owner = User::updateOrCreate(
            ['email' => 'owner@smarthome.local'],
            [
                'name' => 'System Owner',
                'password' => bcrypt('password'),
                'role_id' => $ownerRole?->id,
            ]
        );
        UserAttribute::updateOrCreate(
            ['user_id' => $owner->id],
            [
                'department' => 'Executive',
                'location' => 'HQ',
                'employment_status' => 'Full-time',
                'attributes' => ['role' => 'manager'],
            ]
        );

        // Create sample family member
        $familyRole = Role::where('slug', 'family')->first();
        $family = User::updateOrCreate(
            ['email' => 'family@smarthome.local'],
            [
                'name' => 'Family Member',
                'password' => bcrypt('password'),
                'role_id' => $familyRole?->id,
            ]
        );
        UserAttribute::updateOrCreate(
            ['user_id' => $family->id],
            [
                'department' => 'Household',
                'location' => 'Home',
                'employment_status' => 'Resident',
                'attributes' => ['role' => 'member'],
            ]
        );

        // Create sample guest
        $guestRole = Role::where('slug', 'guest')->first();
        $guest = User::updateOrCreate(
            ['email' => 'guest@smarthome.local'],
            [
                'name' => 'Guest User',
                'password' => bcrypt('password'),
                'role_id' => $guestRole?->id,
            ]
        );
        UserAttribute::updateOrCreate(
            ['user_id' => $guest->id],
            [
                'department' => 'Visitor',
                'location' => 'Guest',
                'employment_status' => 'Temporary',
                'attributes' => ['role' => 'guest'],
            ]
        );
    }
}
