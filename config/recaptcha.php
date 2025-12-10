<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Google reCAPTCHA v2.
    | You can get your site key and secret key from:
    | https://www.google.com/recaptcha/admin
    |
    */

    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    
    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Version
    |--------------------------------------------------------------------------
    |
    | Currently supports 'v2' (checkbox) and 'v3' (invisible).
    | v2 checkbox is recommended for most use cases.
    |
    */
    'version' => env('RECAPTCHA_VERSION', 'v2'),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA API Endpoint
    |--------------------------------------------------------------------------
    |
    | The endpoint to verify reCAPTCHA tokens.
    |
    */
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',

];

