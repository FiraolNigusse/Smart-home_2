<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CaptchaService;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MfaChallengeController extends Controller
{
    public function __construct(
        protected MfaService $mfaService,
        protected CaptchaService $captchaService,
    ) {
    }

    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.mfa-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $rules = [
            'code' => ['required', 'digits:6'],
        ];

        // Only require reCAPTCHA if it's enabled and configured
        if ($this->captchaService->isEnabled()) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        $request->validate($rules);

        // Validate reCAPTCHA if enabled
        if ($this->captchaService->isEnabled()) {
            if (! $this->captchaService->validate($request->input('g-recaptcha-response'), 'mfa')) {
                return back()->withErrors(['g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.']);
            }
        }

        $userId = $request->session()->get('mfa_user_id');

        if (! $userId || ! $user = User::find($userId)) {
            return redirect()->route('login');
        }

        if (! $this->mfaService->verify($user, $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        Auth::login($user);
        $request->session()->forget('mfa_user_id');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}


