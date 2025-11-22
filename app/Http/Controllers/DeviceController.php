<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\RulesEngine;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeviceController extends Controller
{
    protected $rulesEngine;
    protected $auditLogService;

    public function __construct(RulesEngine $rulesEngine, AuditLogService $auditLogService)
    {
        $this->rulesEngine = $rulesEngine;
        $this->auditLogService = $auditLogService;
        // Middleware is already applied in routes/web.php
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $hierarchy = $user->role->hierarchy ?? 0;

        // Filter devices based on user's role hierarchy
        $devices = Device::where('is_active', true)
            ->where('min_role_hierarchy', '<=', $hierarchy)
            ->get();

        return view('devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Device::class);
        return view('devices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Device::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'status' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'min_role_hierarchy' => 'required|integer|min:1|max:3',
        ]);

        $device = Device::create($validated);

        $this->auditLogService->logAllowed(
            auth()->user(),
            $device,
            'create',
            $request,
            ['device_name' => $device->name]
        );

        return redirect()->route('devices.index')
            ->with('success', 'Device created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        $user = auth()->user();
        
        // Check if user can access this device
        if (!$device->isAccessibleBy($user->role->hierarchy ?? 0)) {
            $this->auditLogService->logDenied(
                $user,
                $device,
                'view',
                'Insufficient role hierarchy',
                request()
            );
            abort(403, 'You do not have permission to view this device.');
        }

        $this->auditLogService->logAllowed($user, $device, 'view', request());

        return view('devices.show', compact('device'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device)
    {
        $this->authorize('update', $device);
        return view('devices.edit', compact('device'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device)
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'status' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'min_role_hierarchy' => 'required|integer|min:1|max:3',
            'is_active' => 'boolean',
        ]);

        $device->update($validated);

        $this->auditLogService->logAllowed(
            auth()->user(),
            $device,
            'update',
            $request,
            ['changes' => $validated]
        );

        return redirect()->route('devices.index')
            ->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        $this->authorize('delete', $device);

        $this->auditLogService->logAllowed(
            auth()->user(),
            $device,
            'delete',
            request(),
            ['device_name' => $device->name]
        );

        $device->delete();

        return redirect()->route('devices.index')
            ->with('success', 'Device deleted successfully.');
    }

    /**
     * Control a device (turn on/off, lock/unlock, etc.)
     */
    public function control(Request $request, Device $device)
    {
        $user = auth()->user();
        $action = $request->input('action'); // turn_on, turn_off, lock, unlock, etc.
        $settings = $request->input('settings', []);

        // Check permission using rules engine
        $permission = $this->rulesEngine->checkPermission($user, $device, $action);

        if (!$permission['allowed']) {
            $this->auditLogService->logDenied(
                $user,
                $device,
                $action,
                $permission['message'],
                $request,
                ['settings' => $settings]
            );

            return back()->with('error', $permission['message']);
        }

        // Update device status
        $newStatus = $this->getStatusForAction($action, $device->type);
        $device->status = $newStatus;
        if (!empty($settings)) {
            $currentSettings = $device->settings ?? [];
            $device->settings = array_merge($currentSettings, $settings);
        }
        $device->save();

        $this->auditLogService->logAllowed(
            $user,
            $device,
            $action,
            $request,
            [
                'old_status' => $device->getOriginal('status'),
                'new_status' => $newStatus,
                'settings' => $settings,
            ]
        );

        return back()->with('success', 'Device controlled successfully.');
    }

    /**
     * Get status value for action.
     */
    protected function getStatusForAction(string $action, string $deviceType): string
    {
        $statusMap = [
            'turn_on' => 'on',
            'turn_off' => 'off',
            'lock' => 'locked',
            'unlock' => 'unlocked',
        ];

        return $statusMap[$action] ?? 'unknown';
    }
}
