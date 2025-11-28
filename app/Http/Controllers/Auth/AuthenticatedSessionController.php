<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\CaptchaService;
use App\Services\MfaService;
use App\Services\SessionSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected CaptchaService $captchaService,
        protected MfaService $mfaService,
        protected SessionSecurityService $sessionSecurity,
    ) {
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        $captchaQuestion = $this->captchaService->generate('login');

        return view('auth.login', compact('captchaQuestion'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->validate([
            'captcha_answer' => ['required', 'numeric'],
        ]);

        if (! $this->captchaService->validate($request->input('captcha_answer'), 'login')) {
            return back()
                ->withErrors(['captcha_answer' => 'Incorrect answer to the security question.'])
                ->withInput($request->only('email', 'remember'));
        }

        $request->authenticate();

        if (config('security.mfa.required')) {
            $user = Auth::user();
            $this->mfaService->issue($user);
            Auth::logout();
            $request->session()->put('mfa_user_id', $user->id);

            return redirect()->route('mfa.challenge');
        }

        $request->session()->regenerate();
        
        // Update session metadata
        $this->sessionSecurity->updateSessionMetadata($request->session()->getId(), $request);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
