<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Role Change Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Request a new role</h3>
                    <form method="POST" action="{{ route('role-requests.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Desired Role</label>
                            <select name="requested_role_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Select role</option>
                                @foreach($availableRoles as $role)
                                    <option value="{{ $role->id }}" {{ old('requested_role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }} (Hierarchy {{ $role->hierarchy }})
                                    </option>
                                @endforeach
                            </select>
                            @error('requested_role_id')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Justification</label>
                            <textarea name="justification" rows="4" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('justification') }}</textarea>
                            @error('justification')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="text-right">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Request History</h3>
                    <div class="space-y-3">
                        @forelse($requests as $requestRecord)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $requestRecord->requestedRole->name }} (from {{ $requestRecord->currentRole->name ?? 'N/A' }})
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Submitted {{ $requestRecord->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @class([
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' => $requestRecord->status === 'pending',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $requestRecord->status === 'approved',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $requestRecord->status === 'denied',
                                        ])">
                                        {{ ucfirst($requestRecord->status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">
                                    {{ $requestRecord->justification }}
                                </p>
                                @if($requestRecord->decision_notes)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        Reviewer notes: {{ $requestRecord->decision_notes }}
                                    </p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No requests submitted yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

