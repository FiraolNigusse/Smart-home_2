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

        $captchaQuestion = $this->captchaService->generate('mfa');

        return view('auth.mfa-challenge', compact('captchaQuestion'));
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
            'captcha_answer' => ['required'],
        ]);

        if (! $this->captchaService->validate($request->input('captcha_answer'), 'mfa')) {
            return back()->withErrors(['captcha_answer' => 'Incorrect answer to the security question.']);
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


