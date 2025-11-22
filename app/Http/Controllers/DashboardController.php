<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $hierarchy = $user->role->hierarchy ?? 0;

        // Get accessible devices
        $devices = Device::where('is_active', true)
            ->where('min_role_hierarchy', '<=', $hierarchy)
            ->get();

        // Get recent activity
        $recentLogs = AuditLog::with(['user', 'device'])
            ->where('user_id', $user->id)
            ->orderBy('performed_at', 'desc')
            ->limit(10)
            ->get();

        // Get statistics
        $stats = [
            'total_devices' => $devices->count(),
            'active_devices' => $devices->where('status', '!=', 'off')->count(),
            'recent_actions' => AuditLog::where('user_id', $user->id)
                ->where('performed_at', '>=', now()->subDays(7))
                ->count(),
            'denied_actions' => AuditLog::where('user_id', $user->id)
                ->where('status', 'denied')
                ->where('performed_at', '>=', now()->subDays(7))
                ->count(),
        ];

        // Get device status breakdown
        $deviceStatuses = $devices->groupBy('status')->map->count();

        return view('dashboard', compact('devices', 'recentLogs', 'stats', 'deviceStatuses'));
    }
}
