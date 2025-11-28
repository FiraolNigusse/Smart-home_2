<?php

return [
    'sensitivity_levels' => [
        ['name' => 'Public', 'slug' => 'public', 'hierarchy' => 1, 'description' => 'General information accessible to all authenticated users.'],
        ['name' => 'Internal', 'slug' => 'internal', 'hierarchy' => 2, 'description' => 'Internal-only data for trusted household members.'],
        ['name' => 'Confidential', 'slug' => 'confidential', 'hierarchy' => 3, 'description' => 'Highly sensitive data restricted to system owners.'],
    ],

    'abac_policies' => [
        [
            'name' => 'Payroll Salary Access',
            'conditions' => [
                ['attribute' => 'department', 'operator' => 'equals', 'value' => 'Payroll'],
            ],
            'effect' => 'allow',
            'actions' => ['view_salary'],
        ],
        [
            'name' => 'IT Cannot Access Payroll',
            'conditions' => [
                ['attribute' => 'department', 'operator' => 'equals', 'value' => 'IT'],
            ],
            'effect' => 'deny',
            'actions' => ['view_salary'],
        ],
        [
            'name' => 'Finance Managers Working Hours',
            'conditions' => [
                ['attribute' => 'department', 'operator' => 'equals', 'value' => 'Finance'],
                ['attribute' => 'role', 'operator' => 'in', 'value' => ['manager']],
                ['attribute' => 'time_window', 'operator' => 'between', 'value' => ['08:00', '18:00']],
            ],
            'effect' => 'allow',
            'actions' => ['approve_report'],
        ],
    ],
];

