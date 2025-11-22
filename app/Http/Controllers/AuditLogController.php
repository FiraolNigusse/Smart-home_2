<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AuditLog::with(['user', 'device']);

        // Owners and family can see all logs, guests only see their own
        if ($user->isGuest()) {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('user_id')) {
            if (!$user->isGuest()) {
                $query->where('user_id', $request->user_id);
            }
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('performed_at', 'desc')->paginate(50);

        return view('audit-logs.index', compact('logs'));
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog)
    {
        $user = auth()->user();

        // Guests can only view their own logs
        if ($user->isGuest() && $auditLog->user_id !== $user->id) {
            abort(403, 'You do not have permission to view this log.');
        }

        return view('audit-logs.show', compact('auditLog'));
    }

    /**
     * Export logs as JSON.
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        
        // Only owners can export all logs
        if (!$user->isOwner()) {
            abort(403, 'Only owners can export audit logs.');
        }

        $query = AuditLog::with(['user', 'device']);

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->orderBy('performed_at', 'desc')->get();

        return response()->json($logs, 200, [], JSON_PRETTY_PRINT);
    }
}
