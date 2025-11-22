<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $rule->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('rules.edit', $rule) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Rule Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $rule->name }}</dd>
                                </div>
                                @if($rule->description)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $rule->description }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Applies to Role</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $rule->role ? $rule->role->name : 'All Roles' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Applies to Device</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $rule->device ? $rule->device->name : 'All Devices' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Applies to Action</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $rule->action ? ucfirst(str_replace('_', ' ', $rule->action)) : 'All Actions' }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Rule Conditions</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Condition Type</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ ucfirst(str_replace('_', ' ', $rule->condition_type)) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Condition Parameters</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                                        <pre class="bg-gray-100 dark:bg-gray-900 p-2 rounded text-xs overflow-x-auto">{{ json_encode($rule->condition_params, JSON_PRETTY_PRINT) }}</pre>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Effect</dt>
                                    <dd class="text-sm mt-1">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $rule->effect === 'allow' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ ucfirst($rule->effect) }}
                                        </span>
                                    </dd>
                                </div>
                                @if($rule->denial_message)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Denial Message</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $rule->denial_message }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd class="text-sm mt-1">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $rule->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                            {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $rule->created_at->format('Y-m-d H:i:s') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $rule->updated_at->format('Y-m-d H:i:s') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('rules.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Rules
                </a>
                <a href="{{ route('rules.edit', $rule) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Rule
                </a>
                <form method="POST" action="{{ route('rules.destroy', $rule) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Delete Rule
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


