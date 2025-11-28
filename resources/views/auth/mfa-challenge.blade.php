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

        <div class="mt-4">
            <x-input-label for="captcha_answer" :value="$captchaQuestion ?? __('Security Question')" />
            <x-text-input id="captcha_answer" class="block mt-1 w-full" type="text" name="captcha_answer" required />
            <x-input-error :messages="$errors->get('captcha_answer')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verify & Continue') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>


