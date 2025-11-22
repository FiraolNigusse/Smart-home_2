<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $device->name }}
            </h2>
            @if(auth()->user()->isOwner())
            <div class="flex gap-2">
                <a href="{{ route('devices.edit', $device) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
            </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Device Information</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $device->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($device->type) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $device->location)) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd class="text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $device->status === 'on' || $device->status === 'unlocked' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                            {{ ucfirst($device->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Active</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $device->is_active ? 'Yes' : 'No' }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Device Control</h3>
                            <form method="POST" action="{{ route('devices.control', $device) }}" class="space-y-4">
                                @csrf
                                @if($device->type === 'light')
                                <div>
                                    <button type="submit" name="action" value="turn_on" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Turn On
                                    </button>
                                </div>
                                <div>
                                    <button type="submit" name="action" value="turn_off" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Turn Off
                                    </button>
                                </div>
                                @elseif($device->type === 'lock')
                                <div>
                                    <button type="submit" name="action" value="unlock" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Unlock
                                    </button>
                                </div>
                                <div>
                                    <button type="submit" name="action" value="lock" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Lock
                                    </button>
                                </div>
                                @elseif($device->type === 'thermostat')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Temperature</label>
                                    <input type="number" name="settings[temperature]" value="{{ $device->settings['temperature'] ?? 72 }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                </div>
                                <div>
                                    <button type="submit" name="action" value="update" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Update Temperature
                                    </button>
                                </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h3>
                    <div class="space-y-2">
                        @php
                            $recentLogs = $device->auditLogs()->with('user')->orderBy('performed_at', 'desc')->limit(10)->get();
                        @endphp
                        @forelse($recentLogs as $log)
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                            <div>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">{{ $log->user->name ?? 'System' }}</span>
                                    <span class="font-medium">{{ $log->action }}</span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $log->performed_at->diffForHumans() }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $log->status === 'allowed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 dark:text-gray-400 py-4">No recent activity.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


