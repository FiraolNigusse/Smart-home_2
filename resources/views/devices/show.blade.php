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
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sensitivity Label</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $device->sensitivityLevel?->name ?? 'Not classified' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Minimum Role Hierarchy</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $device->min_role_hierarchy }}
                                    </dd>
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

            @if(auth()->user()->isOwner())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Discretionary Access Permissions</h3>
                    <div class="space-y-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Permissions</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expires</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($permissions as $permission)
                                        <tr>
                                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $permission->target->name }}</td>
                                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                                {{ $permission->can_view ? 'View' : '' }}
                                                {{ $permission->can_control ? ' / Control' : '' }}
                                                @if($permission->allowed_actions)
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ implode(', ', $permission->allowed_actions) }})</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                                {{ $permission->expires_at?->format('Y-m-d') ?? 'Never' }}
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                <form method="POST" action="{{ route('devices.permissions.destroy', [$device, $permission]) }}" onsubmit="return confirm('Revoke this permission?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 text-xs font-semibold">Revoke</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">No discretionary permissions granted.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <form method="POST" action="{{ route('devices.permissions.store', $device) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target User</label>
                                <select name="target_user_id" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">{{ __('Select user') }}</option>
                                    @foreach($users as $userOption)
                                        @continue($userOption->id === auth()->id())
                                        <option value="{{ $userOption->id }}">{{ $userOption->name }} ({{ $userOption->role?->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Allowed Actions (optional)</label>
                                <input type="text" name="allowed_actions[]" placeholder="e.g., unlock" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="can_view" value="1" checked class="rounded border-gray-300 dark:border-gray-700">
                                    <span class="ml-2">Can View</span>
                                </label>
                                <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="can_control" value="1" class="rounded border-gray-300 dark:border-gray-700">
                                    <span class="ml-2">Can Control</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expires At (optional)</label>
                                <input type="date" name="expires_at" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            </div>
                            <div class="md:col-span-2 text-right">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Grant / Update Permission
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

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


