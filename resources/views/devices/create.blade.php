<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Device') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('devices.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Device Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Device Type</label>
                            <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Select Type</option>
                                <option value="light" {{ old('type') === 'light' ? 'selected' : '' }}>Light</option>
                                <option value="lock" {{ old('type') === 'lock' ? 'selected' : '' }}>Lock</option>
                                <option value="thermostat" {{ old('type') === 'thermostat' ? 'selected' : '' }}>Thermostat</option>
                                <option value="camera" {{ old('type') === 'camera' ? 'selected' : '' }}>Camera</option>
                                <option value="door" {{ old('type') === 'door' ? 'selected' : '' }}>Door</option>
                                <option value="sensor" {{ old('type') === 'sensor' ? 'selected' : '' }}>Sensor</option>
                                <option value="control_panel" {{ old('type') === 'control_panel' ? 'selected' : '' }}>Control Panel</option>
                            </select>
                            @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Initial Status</label>
                            <input type="text" name="status" id="status" value="{{ old('status', 'off') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="min_role_hierarchy" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Minimum Role Hierarchy</label>
                            <select name="min_role_hierarchy" id="min_role_hierarchy" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="1" {{ old('min_role_hierarchy', 1) == 1 ? 'selected' : '' }}>Guest (1)</option>
                                <option value="2" {{ old('min_role_hierarchy') == 2 ? 'selected' : '' }}>Family (2)</option>
                                <option value="3" {{ old('min_role_hierarchy') == 3 ? 'selected' : '' }}>Owner (3)</option>
                            </select>
                            @error('min_role_hierarchy')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="sensitivity_level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sensitivity Label</label>
                            <select name="sensitivity_level_id" id="sensitivity_level_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">{{ __('Select sensitivity level') }}</option>
                                @foreach($sensitivityLevels as $level)
                                    <option value="{{ $level->id }}" {{ old('sensitivity_level_id') == $level->id ? 'selected' : '' }}>
                                        {{ $level->name }} ({{ $level->description }})
                                    </option>
                                @endforeach
                            </select>
                            @error('sensitivity_level_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('devices.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Device
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


