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

    'password_policy' => [
        'history_count' => env('PASSWORD_HISTORY_COUNT', 5),
    ],

    'login_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
    'login_decay_seconds' => env('LOGIN_DECAY_SECONDS', 300),

    'captcha' => [
        'enabled' => env('CAPTCHA_ENABLED', true),
    ],

    'mfa' => [
        'required' => env('MFA_REQUIRED', true),
    ],

    'sms' => [
        'enabled' => env('SMS_VERIFICATION_ENABLED', false),
        'api_url' => env('SMS_API_URL'),
        'api_key' => env('SMS_API_KEY'),
    ],

    'session' => [
        'bind_ip' => env('SESSION_BIND_IP', false),
        'bind_device' => env('SESSION_BIND_DEVICE', false),
        'timeout_minutes' => env('SESSION_TIMEOUT_MINUTES', 120),
        'absolute_timeout_minutes' => env('SESSION_ABSOLUTE_TIMEOUT_MINUTES', 480),
    ],

    'headers' => [
        'hsts' => [
            'enabled' => env('HSTS_ENABLED', true),
            'max_age' => env('HSTS_MAX_AGE', 31536000),
            'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
            'preload' => env('HSTS_PRELOAD', false),
        ],

        'content_type_nosniff' => env('HEADER_CONTENT_TYPE_NOSNIFF', true),

        'frame_options' => env('HEADER_FRAME_OPTIONS', 'DENY'),

        'xss_protection' => env('HEADER_XSS_PROTECTION', true),

        'referrer_policy' => env('HEADER_REFERRER_POLICY', 'strict-origin-when-cross-origin'),

        'permissions_policy' => env('HEADER_PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=()'),

        /*
        |--------------------------------------------------------------------------
        | FIXED CSP — reCAPTCHA + Fonts + Secure Defaults
        |--------------------------------------------------------------------------
        */
        'content_security_policy' => env('HEADER_CSP',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com; img-src 'self' data: https://www.gstatic.com https://www.google.com; font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net; frame-src https://www.google.com https://www.gstatic.com; connect-src 'self';"
        ),
    ],
];
