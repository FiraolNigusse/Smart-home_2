# Quick reCAPTCHA Setup Guide

## Problem: reCAPTCHA is not showing

The reCAPTCHA widget won't appear until you add your keys to the `.env` file.

## Quick Fix - Use Test Keys (Development Only)

For immediate testing, add these Google test keys to your `.env` file:

```env
RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
RECAPTCHA_VERSION=v2
```

**Important:** These are test keys that always pass verification. They should ONLY be used for development/testing.

## Steps to Add Keys:

1. Open your `.env` file (in the project root directory)

2. Add these lines:
   ```env
   RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
   RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
   RECAPTCHA_VERSION=v2
   ```

3. Clear Laravel's config cache:
   ```bash
   php artisan config:clear
   ```

4. Refresh your browser - the reCAPTCHA widget should now appear!

## For Production - Get Real Keys

1. Visit: https://www.google.com/recaptcha/admin
2. Click "Create" to register a new site
3. Choose **"reCAPTCHA v2"** → **"I'm not a robot" Checkbox**
4. Add your domain(s) (e.g., `localhost`, `127.0.0.1` for local testing, or your actual domain for production)
5. Copy the **Site Key** and **Secret Key**
6. Replace the test keys in your `.env` file with your real keys

## Verify It's Working

After adding the keys and clearing the config cache:
- Visit the registration page: `http://127.0.0.1:8000/register`
- You should see the Google reCAPTCHA checkbox widget
- If you still see a yellow warning box, check:
  - Keys are correctly added to `.env` (no typos, no extra spaces)
  - You ran `php artisan config:clear`
  - Your `.env` file is in the project root (same directory as `artisan`)

## Troubleshooting

- **Still not showing?** Make sure there are no spaces around the `=` sign in `.env`
- **Config not updating?** Try: `php artisan config:cache` then `php artisan config:clear`
- **Test keys work but real keys don't?** Make sure you added `localhost` or `127.0.0.1` to your reCAPTCHA site's allowed domains in Google's console

