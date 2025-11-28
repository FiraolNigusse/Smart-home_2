<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Biometric Credentials') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('status'))
                        <div class="mb-4 text-sm text-green-600 dark:text-green-300">
                            {{ session('status') }}
                        </div>
                    @endif
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ __('Register public keys from compatible biometric devices (WebAuthn, FIDO, etc.).') }}
                    </p>
                    <form method="POST" action="{{ route('security.biometrics.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Credential Label')" />
                            <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="public_key" :value="__('Public Key Payload')" />
                            <textarea id="public_key" name="public_key" rows="4" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" required></textarea>
                            <x-input-error :messages="$errors->get('public_key')" class="mt-2" />
                        </div>
                        <div class="text-right">
                            <x-primary-button>{{ __('Save Credential') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Registered Devices') }}</h3>
                    <div class="space-y-3">
                        @forelse($credentials as $credential)
                            <form method="POST" action="{{ route('security.biometrics.destroy', $credential) }}" class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded p-3">
                                @csrf
                                @method('DELETE')
                                <div>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $credential->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $credential->public_key_id }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Last used: {{ $credential->last_used_at?->diffForHumans() ?? 'Never' }}</p>
                                </div>
                                <x-secondary-button type="submit">{{ __('Remove') }}</x-secondary-button>
                            </form>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No biometric credentials added yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

