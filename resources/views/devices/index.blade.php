<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Devices') }}
            </h2>
            @if(auth()->user()->isOwner())
            <a href="{{ route('devices.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add Device
            </a>
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($devices as $device)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $device->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $device->location)) }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $device->status === 'on' || $device->status === 'unlocked' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                {{ ucfirst($device->status) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">{{ ucfirst($device->type) }}</p>
                        <div class="flex gap-2">
                            <a href="{{ route('devices.show', $device) }}" class="flex-1 text-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                View
                            </a>
                            @if(auth()->user()->isOwner())
                            <a href="{{ route('devices.edit', $device) }}" class="flex-1 text-center bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Edit
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No devices available.</p>
                    @if(auth()->user()->isOwner())
                    <a href="{{ route('devices.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add Your First Device
                    </a>
                    @endif
                </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>


