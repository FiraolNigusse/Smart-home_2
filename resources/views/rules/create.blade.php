<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create New Rule') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('rules.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rule Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('description') }}</textarea>
                            @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Apply to Role (Leave empty for all roles)</label>
                            <select name="role_id" id="role_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">All Roles</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="device_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Apply to Device (Leave empty for all devices)</label>
                            <select name="device_id" id="device_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">All Devices</option>
                                @foreach($devices as $device)
                                <option value="{{ $device->id }}" {{ old('device_id') == $device->id ? 'selected' : '' }}>{{ $device->name }}</option>
                                @endforeach
                            </select>
                            @error('device_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="action" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Apply to Action (Leave empty for all actions)</label>
                            <input type="text" name="action" id="action" value="{{ old('action') }}" placeholder="e.g., unlock, turn_on" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('action')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="condition_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condition Type</label>
                            <select name="condition_type" id="condition_type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="always" {{ old('condition_type') === 'always' ? 'selected' : '' }}>Always</option>
                                <option value="time_window" {{ old('condition_type') === 'time_window' ? 'selected' : '' }}>Time Window</option>
                                <option value="day_of_week" {{ old('condition_type') === 'day_of_week' ? 'selected' : '' }}>Day of Week</option>
                            </select>
                            @error('condition_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4" id="condition_params">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Condition Parameters (JSON)</label>
                            <textarea name="condition_params" id="condition_params_input" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 font-mono text-sm">{{ old('condition_params', '{}') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                For time_window: {"start_time": "22:00", "end_time": "06:00"}<br>
                                For day_of_week: {"days": [0,1,2,3,4,5,6]} (0=Sunday, 6=Saturday)<br>
                                For always: {}
                            </p>
                            @error('condition_params')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="effect" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Effect</label>
                            <select name="effect" id="effect" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="allow" {{ old('effect') === 'allow' ? 'selected' : '' }}>Allow</option>
                                <option value="deny" {{ old('effect') === 'deny' ? 'selected' : '' }}>Deny</option>
                            </select>
                            @error('effect')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="denial_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Denial Message (shown when rule denies access)</label>
                            <textarea name="denial_message" id="denial_message" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('denial_message') }}</textarea>
                            @error('denial_message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('rules.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


