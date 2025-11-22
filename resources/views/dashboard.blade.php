<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Smart Home Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Devices</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_devices'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Devices</div>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['active_devices'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Recent Actions (7d)</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['recent_actions'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Denied Actions (7d)</div>
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['denied_actions'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Devices Grid -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Your Devices</h3>
                        @if(auth()->user()->isOwner())
                        <a href="{{ route('devices.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add Device
                        </a>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($devices as $device)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $device->name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $device->location)) }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full {{ $device->status === 'on' || $device->status === 'unlocked' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                    {{ ucfirst($device->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">{{ ucfirst($device->type) }}</p>
                            <a href="{{ route('devices.show', $device) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                View Details →
                            </a>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">
                            No devices available.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h3>
                    <div class="space-y-3">
                        @forelse($recentLogs as $log)
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                            <div>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">{{ $log->action }}</span>
                                    @if($log->device)
                                    on <span class="font-medium">{{ $log->device->name }}</span>
                                    @endif
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
                    <div class="mt-4">
                        <a href="{{ route('audit-logs.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                            View All Activity →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
