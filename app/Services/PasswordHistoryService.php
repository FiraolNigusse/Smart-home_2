<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordHistoryService
{
    /**
     * Check if password was recently used.
     */
    public function wasRecentlyUsed(User $user, string $password, int $historyCount = 5): bool
    {
        $history = PasswordHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($historyCount)
            ->get();

        foreach ($history as $record) {
            if (Hash::check($password, $record->password_hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Store password in history.
     */
    public function store(User $user, string $password): void
    {
        $maxHistory = config('security.password_policy.history_count', 5);

        // Store new password
        PasswordHistory::create([
            'user_id' => $user->id,
            'password_hash' => Hash::make($password),
            'created_at' => now(),
        ]);

        // Clean up old history beyond limit
        $oldRecords = PasswordHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->skip($maxHistory)
            ->get();

        foreach ($oldRecords as $record) {
            $record->delete();
        }
    }
}

