<!DOCTYPE html>
<html>
<head>
    <title>reCAPTCHA Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .section { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #fff; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>reCAPTCHA Configuration Debug</h1>
    
    <div class="section">
        <h2>Environment Variables</h2>
        <pre>RECAPTCHA_SITE_KEY: {{ env('RECAPTCHA_SITE_KEY') ?: '(NOT SET)' }}
RECAPTCHA_SECRET_KEY: {{ env('RECAPTCHA_SECRET_KEY') ? '(SET - ' . strlen(env('RECAPTCHA_SECRET_KEY')) . ' chars)' : '(NOT SET)' }}
RECAPTCHA_VERSION: {{ env('RECAPTCHA_VERSION', 'v2') }}</pre>
    </div>
    
    <div class="section">
        <h2>Config Values</h2>
        <pre>config('recaptcha.site_key'): {{ config('recaptcha.site_key') ?: '(EMPTY)' }}
config('recaptcha.secret_key'): {{ config('recaptcha.secret_key') ? '(SET)' : '(EMPTY)' }}
config('recaptcha.version'): {{ config('recaptcha.version', 'v2') }}</pre>
    </div>
    
    <div class="section">
        <h2>Service Check</h2>
        @php
            $captchaService = app(\App\Services\CaptchaService::class);
            $isEnabled = $captchaService->isEnabled();
        @endphp
        <p><strong>isEnabled():</strong> 
            <span class="{{ $isEnabled ? 'success' : 'error' }}">
                {{ $isEnabled ? 'TRUE ✓' : 'FALSE ✗' }}
            </span>
        </p>
        <p><strong>getSiteKey():</strong> {{ $captchaService->getSiteKey() ?: '(EMPTY)' }}</p>
    </div>
    
    <div class="section">
        <h2>View Check</h2>
        @php
            $recaptchaSiteKey = trim(config('recaptcha.site_key', ''));
            $recaptchaSecretKey = trim(config('recaptcha.secret_key', ''));
            $recaptchaEnabled = !empty($recaptchaSiteKey) && !empty($recaptchaSecretKey);
        @endphp
        <p><strong>Site Key (trimmed):</strong> {{ $recaptchaSiteKey ?: '(EMPTY)' }}</p>
        <p><strong>Secret Key (trimmed):</strong> {{ $recaptchaSecretKey ? '(SET)' : '(EMPTY)' }}</p>
        <p><strong>Should show widget:</strong> 
            <span class="{{ $recaptchaEnabled ? 'success' : 'error' }}">
                {{ $recaptchaEnabled ? 'YES ✓' : 'NO ✗' }}
            </span>
        </p>
    </div>
    
    @if($recaptchaEnabled)
    <div class="section">
        <h2>reCAPTCHA Widget Test</h2>
        <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </div>
    @endif
    
    <div class="section">
        <h2>Recommendations</h2>
        @if(!env('RECAPTCHA_SITE_KEY'))
            <p class="error">❌ RECAPTCHA_SITE_KEY is not set in .env file</p>
        @elseif(!config('recaptcha.site_key'))
            <p class="error">❌ Config cache issue - site key not loading from .env</p>
            <p><strong>Solution:</strong> Run <code>php artisan config:clear</code></p>
        @elseif(!env('RECAPTCHA_SECRET_KEY'))
            <p class="error">❌ RECAPTCHA_SECRET_KEY is not set in .env file</p>
        @elseif(!config('recaptcha.secret_key'))
            <p class="error">❌ Config cache issue - secret key not loading from .env</p>
            <p><strong>Solution:</strong> Run <code>php artisan config:clear</code></p>
        @else
            <p class="success">✓ Configuration looks good! Widget should be visible above.</p>
        @endif
    </div>
</body>
</html>

