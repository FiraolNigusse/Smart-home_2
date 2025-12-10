<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('For your security, enter the verification code we sent to your email.') }}
    </div>

    <form method="POST" action="{{ route('mfa.verify') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Verification Code')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" required autofocus autocomplete="one-time-code" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <!-- Google reCAPTCHA -->
        @php
            $recaptchaSiteKey = config('recaptcha.site_key', '');
        @endphp
        @if(!empty($recaptchaSiteKey))
        <div class="mt-4">
            <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
            <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2" />
        </div>
        @endif

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verify & Continue') }}
            </x-primary-button>
        </div>
    </form>

</x-guest-layout>
