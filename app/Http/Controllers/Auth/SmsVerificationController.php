<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsVerificationController extends Controller
{
    public function __construct(
        protected SmsVerificationService $smsService
    ) {
    }

    /**
     * Show SMS verification form.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('sms_verification_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.verify-sms');
    }

    /**
     * Verify SMS code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $userId = $request->session()->get('sms_verification_user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        // In a real implementation, verify code from database/cache
        // For now, this is a placeholder - integrate with your SMS verification storage
        $storedCode = $request->session()->get('sms_verification_code');

        if ($storedCode && $storedCode === $request->input('code')) {
            $user->update(['phone_verified_at' => now()]);
            $request->session()->forget(['sms_verification_user_id', 'sms_verification_code']);

            return redirect()->route('dashboard')
                ->with('status', 'Phone number verified successfully.');
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    /**
     * Resend SMS verification code.
     */
    public function resend(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('sms_verification_user_id');
        $user = User::find($userId);

        if ($user && $user->phone) {
            $code = $this->smsService->generateCode();
            $this->smsService->sendCode($user, $code);
            
            $request->session()->put('sms_verification_code', $code);

            return back()->with('status', 'Verification code sent to your phone.');
        }

        return redirect()->route('login');
    }
}

