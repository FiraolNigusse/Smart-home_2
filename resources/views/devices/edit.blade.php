<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Device') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('devices.update', $device) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Device Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $device->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Device Type</label>
                            <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="light" {{ old('type', $device->type) === 'light' ? 'selected' : '' }}>Light</option>
                                <option value="lock" {{ old('type', $device->type) === 'lock' ? 'selected' : '' }}>Lock</option>
                                <option value="thermostat" {{ old('type', $device->type) === 'thermostat' ? 'selected' : '' }}>Thermostat</option>
                                <option value="camera" {{ old('type', $device->type) === 'camera' ? 'selected' : '' }}>Camera</option>
                                <option value="door" {{ old('type', $device->type) === 'door' ? 'selected' : '' }}>Door</option>
                                <option value="sensor" {{ old('type', $device->type) === 'sensor' ? 'selected' : '' }}>Sensor</option>
                                <option value="control_panel" {{ old('type', $device->type) === 'control_panel' ? 'selected' : '' }}>Control Panel</option>
                            </select>
                            @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                            <input type="text" name="location" id="location" value="{{ old('location', $device->location) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <input type="text" name="status" id="status" value="{{ old('status', $device->status) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="min_role_hierarchy" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Minimum Role Hierarchy</label>
                            <select name="min_role_hierarchy" id="min_role_hierarchy" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="1" {{ old('min_role_hierarchy', $device->min_role_hierarchy) == 1 ? 'selected' : '' }}>Guest (1)</option>
                                <option value="2" {{ old('min_role_hierarchy', $device->min_role_hierarchy) == 2 ? 'selected' : '' }}>Family (2)</option>
                                <option value="3" {{ old('min_role_hierarchy', $device->min_role_hierarchy) == 3 ? 'selected' : '' }}>Owner (3)</option>
                            </select>
                            @error('min_role_hierarchy')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $device->is_active) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('devices.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Device
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


