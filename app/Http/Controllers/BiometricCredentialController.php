<?php

namespace App\Http\Controllers;

use App\Models\BiometricCredential;
use App\Services\SystemLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BiometricCredentialController extends Controller
{
    public function __construct(protected SystemLogService $systemLogService)
    {
    }

    public function index(Request $request): View
    {
        $credentials = $request->user()->biometricCredentials()->latest()->get();

        return view('security.biometrics', compact('credentials'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'public_key' => ['required', 'string'],
        ]);

        $credential = $request->user()->biometricCredentials()->create([
            'name' => $request->input('name'),
            'public_key_id' => 'bio_'.bin2hex(random_bytes(8)),
            'public_key' => $request->input('public_key'),
        ]);

        $this->systemLogService->log('biometric.created', 'info', $request->user(), 'Biometric credential added', [
            'credential_id' => $credential->id,
        ]);

        return back()->with('status', 'Biometric credential added.');
    }

    public function destroy(Request $request, BiometricCredential $credential): RedirectResponse
    {
        abort_unless($credential->user_id === $request->user()->id, 403);

        $credential->delete();

        $this->systemLogService->log('biometric.deleted', 'warning', $request->user(), 'Biometric credential removed', [
            'credential_id' => $credential->id,
        ]);

        return back()->with('status', 'Biometric credential removed.');
    }
}


