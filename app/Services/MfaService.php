<?php

namespace App\Services;

use App\Models\MfaCode;
use App\Models\User;
use App\Notifications\MfaCodeNotification;
use Illuminate\Support\Facades\Notification;

class MfaService
{
    public function issue(User $user): MfaCode
    {
        $code = random_int(100000, 999999);

        $record = MfaCode::create([
            'user_id' => $user->id,
            'channel' => 'email',
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        Notification::send($user, new MfaCodeNotification($code));

        logger()->info("MFA code for {$user->email}: {$code}");

        return $record;
    }

    public function verify(User $user, string $code): bool
    {
        $record = MfaCode::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->where('expires_at', '>=', now())
            ->latest()
            ->first();

        if (! $record) {
            return false;
        }

        if (! hash_equals($record->code, $code)) {
            return false;
        }

        $record->consume();

        return true;
    }
}


