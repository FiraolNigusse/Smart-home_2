<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\WebAuthnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthnController extends Controller
{
    public function __construct(
        protected WebAuthnService $webauthnService
    ) {
    }

    /**
     * Get registration challenge.
     */
    public function registrationChallenge(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $challenge = $this->webauthnService->generateRegistrationChallenge($user);

        return response()->json($challenge);
    }

    /**
     * Complete registration.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'credential' => ['required', 'array'],
            'credential.id' => ['required'],
            'credential.response' => ['required', 'array'],
            'challenge' => ['required', 'string'],
        ]);

        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $credential = $this->webauthnService->verifyRegistration(
            $user,
            $request->input('credential'),
            $request->input('challenge')
        );

        if (!$credential) {
            return response()->json(['error' => 'Registration verification failed'], 400);
        }

        return response()->json([
            'success' => true,
            'credential_id' => $credential->public_key_id,
        ]);
    }

    /**
     * Get authentication challenge.
     */
    public function authenticationChallenge(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $challenge = $this->webauthnService->generateAuthenticationChallenge($user);

        return response()->json($challenge);
    }

    /**
     * Complete authentication.
     */
    public function authenticate(Request $request): JsonResponse
    {
        $request->validate([
            'credential' => ['required', 'array'],
            'credential.id' => ['required'],
            'challenge' => ['required', 'string'],
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $verified = $this->webauthnService->verifyAuthentication(
            $user,
            $request->input('credential'),
            $request->input('challenge')
        );

        if (!$verified) {
            return response()->json(['error' => 'Authentication verification failed'], 400);
        }

        return response()->json(['success' => true]);
    }
}

