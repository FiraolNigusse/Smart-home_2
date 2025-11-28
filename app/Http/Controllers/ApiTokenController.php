<?php

namespace App\Http\Controllers;

use App\Services\SystemLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function __construct(protected SystemLogService $systemLogService)
    {
    }

    public function index(Request $request): View
    {
        $tokens = $request->user()->tokens()->latest()->get();

        return view('security.tokens', compact('tokens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'string'],
        ]);

        $abilitiesInput = trim((string) $request->input('abilities'));
        $abilities = $abilitiesInput ? array_filter(array_map('trim', explode(',', $abilitiesInput))) : [];

        $token = $request->user()->createToken(
            $request->input('name'),
            $abilities ?: ['*']
        );

        $this->systemLogService->log('token.created', 'info', $request->user(), 'API token issued', [
            'token_name' => $request->input('name'),
        ]);

        return back()->with('new_token', $token->plainTextToken);
    }

    public function destroy(Request $request, string $tokenId): RedirectResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        $this->systemLogService->log('token.revoked', 'warning', $request->user(), 'API token revoked', [
            'token_id' => $tokenId,
        ]);

        return back()->with('status', 'Token revoked.');
    }
}

