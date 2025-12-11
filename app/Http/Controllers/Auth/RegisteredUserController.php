<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CaptchaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(protected CaptchaService $captchaService)
    {
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'password' => [
                'required',
                'confirmed',
                Password::min(config('security.password_rules.min'))
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ];

        // Only require reCAPTCHA if it's enabled and configured
        if ($this->captchaService->isEnabled()) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        $request->validate($rules);

        // Validate reCAPTCHA if enabled
        if ($this->captchaService->isEnabled()) {
            if (! $this->captchaService->validate($request->input('g-recaptcha-response'), 'register')) {
            return back()
                    ->withErrors(['g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.'])
                ->withInput($request->except('password', 'password_confirmation'));
            }
        }

        // Assign default "Guest" role to new users
        $guestRole = \App\Models\Role::where('slug', 'guest')->first();
        if (!$guestRole) {
            throw new \RuntimeException('Guest role not found. Please run database seeders.');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => $guestRole->id,
        ]);

        event(new Registered($user));

        // Explicitly send email verification notification
        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
