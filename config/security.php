<?php

return [
    'force_https' => env('FORCE_HTTPS', false),
    'alert_min_severity' => env('ALERT_MIN_SEVERITY', 'warning'),
    'password_rules' => [
        'min' => 12,
        'mixed_case' => true,
        'numbers' => true,
        'symbols' => true,
    ],
    'login_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
    'login_decay_seconds' => env('LOGIN_DECAY_SECONDS', 300),
    'captcha' => [
        'enabled' => env('CAPTCHA_ENABLED', true),
    ],
    'mfa' => [
        'required' => env('MFA_REQUIRED', true),
    ],
];


