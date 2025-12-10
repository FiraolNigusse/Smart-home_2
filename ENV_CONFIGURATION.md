# Environment Configuration Guide

This document outlines the required environment variables for the Smart Home application, including SMTP email settings and Google reCAPTCHA configuration.

## SMTP Email Configuration

To enable email sending (for email verification, notifications, etc.), add the following to your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Common SMTP Providers

#### Gmail
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Note:** Gmail requires an "App Password" instead of your regular password. Generate one at: https://myaccount.google.com/apppasswords

#### Mailgun
```env
MAIL_MAILER=mailgun
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### SendGrid
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Microsoft 365 / Outlook
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Google reCAPTCHA Configuration

To enable Google reCAPTCHA (replacing the math-based CAPTCHA), you need to:

1. **Get reCAPTCHA keys** from Google:
   - Visit: https://www.google.com/recaptcha/admin
   - Click "Create" to register a new site
   - Choose "reCAPTCHA v2" → "I'm not a robot" Checkbox
   - Add your domain(s)
   - Copy the **Site Key** and **Secret Key**

2. **Add to your `.env` file**:

```env
RECAPTCHA_SITE_KEY=your-recaptcha-site-key
RECAPTCHA_SECRET_KEY=your-recaptcha-secret-key
RECAPTCHA_VERSION=v2
```

### Testing reCAPTCHA Locally

For local development, you can use Google's test keys:

```env
RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
RECAPTCHA_VERSION=v2
```

**Note:** These test keys always pass verification but should only be used in development!

## Complete .env Configuration Template

Here's a complete template with all email and reCAPTCHA settings:

```env
APP_NAME="Smart Home"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smarthome
DB_USERNAME=root
DB_PASSWORD=

# SMTP Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Google reCAPTCHA
RECAPTCHA_SITE_KEY=your-recaptcha-site-key
RECAPTCHA_SECRET_KEY=your-recaptcha-secret-key
RECAPTCHA_VERSION=v2

# Security Settings
FORCE_HTTPS=false
CAPTCHA_ENABLED=true
MFA_REQUIRED=true
LOGIN_MAX_ATTEMPTS=5
LOGIN_DECAY_SECONDS=300
```

## Verification

After configuring your `.env` file:

1. **Clear config cache** (if in production):
   ```bash
   php artisan config:clear
   ```

2. **Test email configuration**:
   - Register a new user
   - Check if verification email is sent
   - Check logs at `storage/logs/laravel.log` if emails aren't being sent

3. **Test reCAPTCHA**:
   - Visit the registration or login page
   - You should see the Google reCAPTCHA widget instead of the math question
   - Try submitting the form to verify it's working

## Troubleshooting

### Email Issues

- **Emails not sending**: Check your SMTP credentials and ensure your hosting provider allows outbound SMTP connections
- **Emails going to spam**: Set up SPF, DKIM, and DMARC records for your domain
- **Connection timeout**: Verify firewall settings and port accessibility (587 for TLS, 465 for SSL)

### reCAPTCHA Issues

- **Widget not showing**: Check browser console for errors, verify the site key is correct
- **Verification failing**: Verify the secret key matches your site key, check server logs for reCAPTCHA API errors
- **Localhost not working**: Use Google's test keys or add `localhost` to your reCAPTCHA site's allowed domains

