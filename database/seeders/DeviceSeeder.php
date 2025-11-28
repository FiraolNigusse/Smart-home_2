<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\SensitivityLevel;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = [
            [
                'name' => 'Front Door Lock',
                'type' => 'lock',
                'location' => 'front_door',
                'status' => 'locked',
                'settings' => ['auto_lock' => true, 'lock_timeout' => 30],
                'is_active' => true,
                'min_role_hierarchy' => 1, // All roles can access
            ],
            [
                'name' => 'Living Room Light',
                'type' => 'light',
                'location' => 'living_room',
                'status' => 'off',
                'settings' => ['brightness' => 50, 'color' => 'warm_white'],
                'is_active' => true,
                'min_role_hierarchy' => 1,
            ],
            [
                'name' => 'Master Bedroom Thermostat',
                'type' => 'thermostat',
                'location' => 'master_bedroom',
                'status' => 'on',
                'settings' => ['temperature' => 72, 'mode' => 'auto'],
                'is_active' => true,
                'min_role_hierarchy' => 2, // Family and above
            ],
            [
                'name' => 'Security Camera - Front',
                'type' => 'camera',
                'location' => 'front_yard',
                'status' => 'on',
                'settings' => ['recording' => true, 'motion_detection' => true],
                'is_active' => true,
                'min_role_hierarchy' => 2, // Family and above
            ],
            [
                'name' => 'Garage Door',
                'type' => 'door',
                'location' => 'garage',
                'status' => 'closed',
                'settings' => ['auto_close' => true],
                'is_active' => true,
                'min_role_hierarchy' => 2, // Family and above
            ],
            [
                'name' => 'Back Door Lock',
                'type' => 'lock',
                'location' => 'back_door',
                'status' => 'locked',
                'settings' => ['auto_lock' => true],
                'is_active' => true,
                'min_role_hierarchy' => 1,
            ],
            [
                'name' => 'Kitchen Light',
                'type' => 'light',
                'location' => 'kitchen',
                'status' => 'off',
                'settings' => ['brightness' => 75],
                'is_active' => true,
                'min_role_hierarchy' => 1,
            ],
            [
                'name' => 'System Control Panel',
                'type' => 'control_panel',
                'location' => 'main_hall',
                'status' => 'on',
                'settings' => [],
                'is_active' => true,
                'min_role_hierarchy' => 3, // Owner only
            ],
        ];

        foreach ($devices as $device) {
            $levelSlug = match ($device['type']) {
                'control_panel', 'camera' => 'confidential',
                'lock', 'door', 'thermostat' => 'internal',
                default => 'public',
            };

            $device['sensitivity_level_id'] = SensitivityLevel::where('slug', $levelSlug)->value('id');

            Device::updateOrCreate(
                ['name' => $device['name'], 'location' => $device['location']],
                $device
            );
        }
    }
}
