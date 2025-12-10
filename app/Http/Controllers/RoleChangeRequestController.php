<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RoleChangeRequest;
use App\Services\AuditLogService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;

class RoleChangeRequestController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected SystemLogService $systemLogService,
    ) {
    }

    public function index()
    {
        // Owners don't need to request role changes - they should use Review Requests instead
        if (auth()->user()->isOwner()) {
            return redirect()->route('role-requests.review');
        }

        $requests = auth()->user()
            ->roleChangeRequests()
            ->with(['requestedRole', 'reviewer'])
            ->latest()
            ->get();

        $availableRoles = Role::orderBy('hierarchy', 'desc')->get();

        return view('roles.requests.index', compact('requests', 'availableRoles'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'requested_role_id' => 'required|exists:roles,id',
            'justification' => 'required|string|min:10',
        ]);

        if ($user->role_id === (int) $validated['requested_role_id']) {
            return back()->withErrors(['requested_role_id' => 'You already have this role.']);
        }

        $roleRequest = RoleChangeRequest::create([
            'user_id' => $user->id,
            'current_role_id' => $user->role_id,
            'requested_role_id' => $validated['requested_role_id'],
            'justification' => $validated['justification'],
            'status' => 'pending',
        ]);

        $this->systemLogService->log(
            eventType: 'role.request.submitted',
            severity: 'info',
            actor: $user,
            message: 'Role change request submitted',
            context: ['requested_role_id' => $validated['requested_role_id']]
        );

        return back()->with('success', 'Role change request submitted for review.');
    }

    public function reviewIndex()
    {
        $this->ensureOwner();

        $requests = RoleChangeRequest::with(['user', 'requestedRole', 'currentRole'])
            ->orderBy('status')
            ->orderByDesc('created_at')
            ->get();

        return view('roles.requests.review', compact('requests'));
    }

    public function approve(Request $request, RoleChangeRequest $roleChangeRequest)
    {
        $this->ensureOwner();

        if ($roleChangeRequest->status !== 'pending') {
            return back()->withErrors(['request' => 'This request has already been processed.']);
        }

        $roleChangeRequest->update([
            'status' => 'approved',
            'reviewer_id' => auth()->id(),
            'decision_notes' => $request->input('decision_notes'),
            'reviewed_at' => now(),
        ]);

        $oldRoleId = $roleChangeRequest->user->role_id;
        $roleChangeRequest->user->update(['role_id' => $roleChangeRequest->requested_role_id]);

        $this->auditLogService->logAllowed(
            auth()->user(),
            null,
            'role_change_approve',
            $request,
            ['request_id' => $roleChangeRequest->id]
        );

        $this->systemLogService->log(
            eventType: 'role.changed',
            severity: 'warning',
            actor: auth()->user(),
            message: 'User role changed',
            context: [
                'user_id' => $roleChangeRequest->user->id,
                'user_email' => $roleChangeRequest->user->email,
                'old_role_id' => $oldRoleId,
                'new_role_id' => $roleChangeRequest->requested_role_id,
                'request_id' => $roleChangeRequest->id,
            ],
            sensitivePayload: [
                'old_role' => $roleChangeRequest->currentRole->name ?? null,
                'new_role' => $roleChangeRequest->requestedRole->name ?? null,
            ]
        );

        $this->systemLogService->log(
            eventType: 'role.request.approved',
            severity: 'info',
            actor: auth()->user(),
            message: 'Role change approved',
            context: ['request_id' => $roleChangeRequest->id]
        );

        return back()->with('success', 'Role change request approved.');
    }

    public function deny(Request $request, RoleChangeRequest $roleChangeRequest)
    {
        $this->ensureOwner();

        if ($roleChangeRequest->status !== 'pending') {
            return back()->withErrors(['request' => 'This request has already been processed.']);
        }

        $roleChangeRequest->update([
            'status' => 'denied',
            'reviewer_id' => auth()->id(),
            'decision_notes' => $request->input('decision_notes'),
            'reviewed_at' => now(),
        ]);

        $this->systemLogService->log(
            eventType: 'role.request.denied',
            severity: 'warning',
            actor: auth()->user(),
            message: 'Role change denied',
            context: ['request_id' => $roleChangeRequest->id]
        );

        return back()->with('success', 'Role change request denied.');
    }

    protected function ensureOwner(): void
    {
        abort_unless(auth()->user()?->isOwner(), 403);
    }
}
