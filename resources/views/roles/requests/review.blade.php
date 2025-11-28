<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Review Role Change Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">From</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">To</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Justification</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($requests as $roleRequest)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                            {{ $roleRequest->user->name }}
                                        </td>
                                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                            {{ $roleRequest->currentRole->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                            {{ $roleRequest->requestedRole->name }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 text-xs rounded-full
                                                @class([
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' => $roleRequest->status === 'pending',
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $roleRequest->status === 'approved',
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $roleRequest->status === 'denied',
                                                ])">
                                                {{ ucfirst($roleRequest->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                            {{ $roleRequest->justification }}
                                            @if($roleRequest->decision_notes)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Reviewer: {{ $roleRequest->decision_notes }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right space-x-2">
                                            @if($roleRequest->status === 'pending')
                                                <form method="POST" action="{{ route('role-requests.approve', $roleRequest) }}" class="inline-flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="text" name="decision_notes" placeholder="Notes" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-xs">
                                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white text-xs font-bold py-1 px-3 rounded">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('role-requests.deny', $roleRequest) }}" class="inline-flex items-center gap-2 mt-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="text" name="decision_notes" placeholder="Notes" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-xs">
                                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-3 rounded">
                                                        Deny
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    Reviewed {{ $roleRequest->reviewed_at?->diffForHumans() }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No role change requests submitted.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

