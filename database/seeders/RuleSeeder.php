<?php

namespace Database\Seeders;

use App\Models\Rule;
use App\Models\Role;
use App\Models\Device;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guestRole = Role::where('slug', 'guest')->first();
        $frontDoorLock = Device::where('name', 'Front Door Lock')->first();
        $backDoorLock = Device::where('name', 'Back Door Lock')->first();
        $garageDoor = Device::where('name', 'Garage Door')->first();

        $rules = [
            [
                'name' => 'Guests cannot unlock doors after 10 PM',
                'description' => 'Prevents guests from unlocking doors during late hours',
                'role_id' => $guestRole?->id,
                'device_id' => null, // Applies to all locks
                'action' => 'unlock',
                'condition_type' => 'time_window',
                'condition_params' => [
                    'start_time' => '22:00',
                    'end_time' => '06:00',
                ],
                'is_active' => true,
                'effect' => 'deny',
                'denial_message' => 'Guests cannot unlock doors between 10 PM and 6 AM for security reasons.',
            ],
            [
                'name' => 'Guests cannot access garage door',
                'description' => 'Guests are not allowed to open the garage door',
                'role_id' => $guestRole?->id,
                'device_id' => $garageDoor?->id,
                'action' => null, // All actions
                'condition_type' => 'always',
                'condition_params' => [],
                'is_active' => true,
                'effect' => 'deny',
                'denial_message' => 'Guests do not have permission to access the garage door.',
            ],
            [
                'name' => 'Guests can only control lights during day',
                'description' => 'Guests can only turn lights on/off during daytime hours',
                'role_id' => $guestRole?->id,
                'device_id' => null,
                'action' => 'turn_on',
                'condition_type' => 'time_window',
                'condition_params' => [
                    'start_time' => '20:00',
                    'end_time' => '07:00',
                ],
                'is_active' => true,
                'effect' => 'deny',
                'denial_message' => 'Guests can only control lights during daytime hours (7 AM - 8 PM).',
            ],
        ];

        foreach ($rules as $rule) {
            if ($rule['role_id'] && ($rule['device_id'] === null || $rule['device_id'])) {
                Rule::updateOrCreate(
                    [
                        'name' => $rule['name'],
                        'role_id' => $rule['role_id'],
                        'device_id' => $rule['device_id'],
                    ],
                    $rule
                );
            }
        }
    }
}
