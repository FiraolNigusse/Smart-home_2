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
                        {{ __('Register biometric devices using WebAuthn/FIDO2. Click "Register Device" to use your browser\'s built-in biometric authentication, or manually enter a public key below.') }}
                    </p>
                    
                    <!-- WebAuthn Registration -->
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('WebAuthn Registration') }}</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                            {{ __('Use your device\'s biometric authentication (fingerprint, face recognition, etc.) to register.') }}
                        </p>
                        <button 
                            type="button" 
                            id="webauthn-register-btn"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm"
                        >
                            {{ __('Register Device with WebAuthn') }}
                        </button>
                        <div id="webauthn-status" class="mt-2 text-sm"></div>
                    </div>

                    <!-- Manual Registration (Fallback) -->
                    <details class="mb-4">
                        <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                            {{ __('Or register manually (Advanced)') }}
                        </summary>
                        <form method="POST" action="{{ route('security.biometrics.store') }}" class="space-y-4 mt-4">
                            @csrf
                            <div>
                                <x-input-label for="name" :value="__('Credential Label')" />
                                <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="public_key" :value="__('Public Key Payload (JSON)')" />
                                <textarea id="public_key" name="public_key" rows="4" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder='{"id":"...","publicKey":"..."}'></textarea>
                                <x-input-error :messages="$errors->get('public_key')" class="mt-2" />
                            </div>
                            <div class="text-right">
                                <x-primary-button>{{ __('Save Credential Manually') }}</x-primary-button>
                            </div>
                        </form>
                    </details>
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

    @push('scripts')
    <script>
        document.getElementById('webauthn-register-btn')?.addEventListener('click', async function() {
            const statusEl = document.getElementById('webauthn-status');
            statusEl.textContent = '{{ __("Requesting challenge...") }}';
            
            try {
                // Get registration challenge
                const challengeResponse = await fetch('{{ route("webauthn.registration.challenge") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'include'
                });
                
                if (!challengeResponse.ok) {
                    throw new Error('Failed to get challenge');
                }
                
                const challenge = await challengeResponse.json();
                statusEl.textContent = '{{ __("Please authenticate with your device...") }}';
                
                // Create credential
                const credential = await navigator.credentials.create({
                    publicKey: challenge
                });
                
                statusEl.textContent = '{{ __("Verifying registration...") }}';
                
                // Send credential to server
                const registerResponse = await fetch('{{ route("webauthn.registration") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        credential: {
                            id: credential.id,
                            rawId: Array.from(new Uint8Array(credential.rawId)),
                            response: {
                                clientDataJSON: Array.from(new Uint8Array(credential.response.clientDataJSON)),
                                attestationObject: Array.from(new Uint8Array(credential.response.attestationObject))
                            },
                            type: credential.type
                        },
                        challenge: challenge.challenge
                    })
                });
                
                if (registerResponse.ok) {
                    statusEl.textContent = '{{ __("Device registered successfully!") }}';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error('Registration failed');
                }
            } catch (error) {
                statusEl.textContent = '{{ __("Error: ") }}' + error.message;
                console.error('WebAuthn error:', error);
            }
        });
    </script>
    @endpush
</x-app-layout>

