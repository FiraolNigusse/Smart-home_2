<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('We sent a verification code to your phone number. Please enter it below.') }}
    </div>

    <form method="POST" action="{{ route('sms.verify.submit') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Verification Code')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" required autofocus autocomplete="one-time-code" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verify Phone Number') }}
            </x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('sms.resend') }}" class="mt-4">
        @csrf
        <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
            {{ __('Resend Code') }}
        </button>
    </form>
</x-guest-layout>

