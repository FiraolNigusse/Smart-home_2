<?php

namespace App\Services;

use App\Models\BiometricCredential;
use App\Models\User;
use Illuminate\Support\Str;

class WebAuthnService
{
    /**
     * Generate WebAuthn registration challenge.
     */
    public function generateRegistrationChallenge(User $user): array
    {
        $challenge = base64_encode(Str::random(32));
        $userId = base64_encode($user->id);
        $userName = $user->email;
        $userDisplayName = $user->name;

        // Store challenge in session for validation
        session(['webauthn_registration_challenge' => $challenge]);

        return [
            'challenge' => $challenge,
            'rp' => [
                'name' => config('app.name', 'Smart Home'),
                'id' => parse_url(config('app.url'), PHP_URL_HOST),
            ],
            'user' => [
                'id' => $userId,
                'name' => $userName,
                'displayName' => $userDisplayName,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7], // ES256
                ['type' => 'public-key', 'alg' => -257], // RS256
            ],
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform', // or 'cross-platform'
                'userVerification' => 'preferred',
                'requireResidentKey' => false,
            ],
            'timeout' => 60000,
            'attestation' => 'none',
        ];
    }

    /**
     * Generate WebAuthn authentication challenge.
     */
    public function generateAuthenticationChallenge(User $user): array
    {
        $challenge = base64_encode(Str::random(32));
        $credentials = $user->biometricCredentials()->get();

        // Store challenge in session
        session(['webauthn_authentication_challenge' => $challenge]);

        $allowCredentials = $credentials->map(function ($credential) {
            return [
                'id' => base64_decode($credential->public_key_id),
                'type' => 'public-key',
            ];
        })->toArray();

        return [
            'challenge' => $challenge,
            'timeout' => 60000,
            'rpId' => parse_url(config('app.url'), PHP_URL_HOST),
            'allowCredentials' => $allowCredentials,
            'userVerification' => 'preferred',
        ];
    }

    /**
     * Verify and store WebAuthn registration.
     */
    public function verifyRegistration(User $user, array $credentialData, string $challenge): ?BiometricCredential
    {
        $storedChallenge = session('webauthn_registration_challenge');
        
        if (!$storedChallenge || $storedChallenge !== $challenge) {
            return null;
        }

        // In production, verify the attestation signature here
        // For now, we'll store the credential data
        $credentialId = base64_encode($credentialData['id'] ?? Str::random(32));
        $publicKey = json_encode($credentialData['response']['publicKey'] ?? []);

        $credential = $user->biometricCredentials()->create([
            'name' => 'WebAuthn Device',
            'public_key_id' => $credentialId,
            'public_key' => $publicKey,
        ]);

        session()->forget('webauthn_registration_challenge');

        return $credential;
    }

    /**
     * Verify WebAuthn authentication.
     */
    public function verifyAuthentication(User $user, array $credentialData, string $challenge): bool
    {
        $storedChallenge = session('webauthn_authentication_challenge');
        
        if (!$storedChallenge || $storedChallenge !== $challenge) {
            return false;
        }

        $credentialId = base64_encode($credentialData['id'] ?? '');
        
        $credential = $user->biometricCredentials()
            ->where('public_key_id', $credentialId)
            ->first();

        if (!$credential) {
            return false;
        }

        // In production, verify the assertion signature here
        // For now, we'll just update last_used_at
        $credential->update(['last_used_at' => now()]);

        session()->forget('webauthn_authentication_challenge');

        return true;
    }
}

