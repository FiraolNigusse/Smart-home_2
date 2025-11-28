<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function __construct(
        protected PasswordHistoryService $passwordHistoryService
    ) {
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $passwordRule = Password::min(config('security.password_rules.min'))
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', $passwordRule, 'confirmed'],
        ]);

        $user = $request->user();

        // Check password history
        $historyCount = config('security.password_policy.history_count', 5);
        if ($this->passwordHistoryService->wasRecentlyUsed($user, $validated['password'], $historyCount)) {
            throw ValidationException::withMessages([
                'password' => ['You cannot reuse your last ' . $historyCount . ' passwords.'],
            ]);
        }

        // Store old password in history before updating
        $this->passwordHistoryService->store($user, $user->password);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
