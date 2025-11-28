<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SensitivityLevel;

class SensitivityLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = config('access.sensitivity_levels', []);

        foreach ($levels as $index => $level) {
            SensitivityLevel::updateOrCreate(
                ['slug' => $level['slug']],
                [
                    'name' => $level['name'],
                    'description' => $level['description'] ?? null,
                    'hierarchy' => $level['hierarchy'] ?? ($index + 1),
                    'is_system_defined' => true,
                ]
            );
        }
    }
}
