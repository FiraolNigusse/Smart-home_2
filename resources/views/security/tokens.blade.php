<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('API Tokens') }}
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
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Create Token</h3>
                    <form method="POST" action="{{ route('security.tokens.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Token Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="abilities" :value="__('Abilities (optional, comma separated)')" />
                            <x-text-input id="abilities" class="block mt-1 w-full" type="text" name="abilities" placeholder="e.g. devices:view,devices:control" />
                            <x-input-error :messages="$errors->get('abilities')" class="mt-2" />
                        </div>
                        <div class="text-right">
                            <x-primary-button>{{ __('Generate Token') }}</x-primary-button>
                        </div>
                    </form>
                    @if (session('new_token'))
                        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900 rounded text-sm text-green-800 dark:text-green-100">
                            {{ __('Copy your new token now:') }}<br>
                            <code class="break-all">{{ session('new_token') }}</code>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Existing Tokens</h3>
                    <div class="space-y-3">
                        @forelse($tokens as $token)
                            <form method="POST" action="{{ route('security.tokens.destroy', $token->id) }}" class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded p-3">
                                @csrf
                                @method('DELETE')
                                <div>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $token->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Last used: {{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</p>
                                </div>
                                <x-secondary-button type="submit">{{ __('Revoke') }}</x-secondary-button>
                            </form>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No tokens issued.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

