<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DevicePermission;
use App\Models\PermissionLog;
use App\Models\SensitivityLevel;
use App\Models\User;
use App\Services\AccessDecisionService;
use App\Services\AuditLogService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(
        protected AccessDecisionService $accessDecisionService,
        protected AuditLogService $auditLogService,
        protected SystemLogService $systemLogService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $hierarchy = $user->role->hierarchy ?? 0;
        $clearance = $user->clearanceHierarchy();

        $permittedDeviceIds = DevicePermission::where('target_user_id', $user->id)->pluck('device_id');

        $devices = Device::with('sensitivityLevel')
            ->where('is_active', true)
            ->where(function ($query) use ($hierarchy, $clearance, $permittedDeviceIds) {
                $query->where(function ($inner) use ($hierarchy, $clearance) {
                    $inner->where('min_role_hierarchy', '<=', $hierarchy)
                        ->where(function ($classificationQuery) use ($clearance) {
                            $classificationQuery->whereNull('sensitivity_level_id')
                                ->orWhereHas('sensitivityLevel', function ($subQuery) use ($clearance) {
                                    $subQuery->where('hierarchy', '<=', $clearance);
                                });
                        });
                })
                ->orWhereIn('id', $permittedDeviceIds);
            })
            ->get();

        return view('devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Device::class);
        $sensitivityLevels = SensitivityLevel::orderBy('hierarchy')->get();
        return view('devices.create', compact('sensitivityLevels'));
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
            'sensitivity_level_id' => 'nullable|exists:sensitivity_levels,id',
        ]);

        $device = Device::create($validated);

        $this->auditLogService->logAllowed(
            auth()->user(),
            $device,
            'create',
            $request,
            ['device_name' => $device->name]
        );

        $this->systemLogService->log(
            eventType: 'device.created',
            severity: 'info',
            actor: auth()->user(),
            message: 'Device created',
            context: ['device_id' => $device->id]
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
        
        $decision = $this->accessDecisionService->evaluate($user, $device, 'view');

        if (!$decision['allowed']) {
            $this->auditLogService->logDenied(
                $user,
                $device,
                'view',
                $decision['message'] ?? 'Access denied',
                request()
            );
            abort(403, 'You do not have permission to view this device.');
        }

        $this->auditLogService->logAllowed($user, $device, 'view', request());

        $permissions = $device->permissions()->with('target')->get();
        $users = User::orderBy('name')->get();
        $sensitivityLevels = SensitivityLevel::orderBy('hierarchy')->get();

        return view('devices.show', compact('device', 'permissions', 'users', 'sensitivityLevels'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device)
    {
        $this->authorize('update', $device);
        $sensitivityLevels = SensitivityLevel::orderBy('hierarchy')->get();
        return view('devices.edit', compact('device', 'sensitivityLevels'));
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
            'sensitivity_level_id' => 'nullable|exists:sensitivity_levels,id',
        ]);

        $device->update($validated);

        $this->auditLogService->logAllowed(
            auth()->user(),
            $device,
            'update',
            $request,
            ['changes' => $validated]
        );

        $this->systemLogService->log(
            eventType: 'device.updated',
            severity: 'info',
            actor: auth()->user(),
            message: 'Device updated',
            context: ['device_id' => $device->id]
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

        $this->systemLogService->log(
            eventType: 'device.deleted',
            severity: 'warning',
            actor: auth()->user(),
            message: 'Device deleted',
            context: ['device_id' => $device->id]
        );

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

        $context = [
            'location' => $request->input('location_override'),
            'ip' => $request->ip(),
        ];

        $permission = $this->accessDecisionService->evaluate($user, $device, $action, $context);

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

    public function grantPermission(Request $request, Device $device)
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id|not_in:' . auth()->id(),
            'can_control' => 'sometimes|boolean',
            'can_view' => 'sometimes|boolean',
            'allowed_actions' => 'nullable|array',
            'allowed_actions.*' => 'string',
            'expires_at' => 'nullable|date',
        ]);

        $allowedActions = collect($validated['allowed_actions'] ?? [])
            ->filter(fn ($value) => filled($value))
            ->values()
            ->all();

        $permission = DevicePermission::updateOrCreate(
            [
                'device_id' => $device->id,
                'target_user_id' => $validated['target_user_id'],
            ],
            [
                'owner_user_id' => auth()->id(),
                'can_view' => $validated['can_view'] ?? true,
                'can_control' => $validated['can_control'] ?? false,
                'allowed_actions' => $allowedActions,
                'expires_at' => $validated['expires_at'] ?? null,
            ]
        );

        $this->logPermissionChange('granted', $permission, [
            'allowed_actions' => $allowedActions,
            'can_view' => $validated['can_view'] ?? true,
            'can_control' => $validated['can_control'] ?? false,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return back()->with('success', 'Permission updated.');
    }

    public function revokePermission(Device $device, DevicePermission $permission)
    {
        $this->authorize('update', $device);

        if ($permission->device_id !== $device->id) {
            abort(404);
        }

        $permission->delete();

        $this->logPermissionChange('revoked', $permission);

        return back()->with('success', 'Permission revoked.');
    }

    protected function logPermissionChange(string $action, DevicePermission $permission, array $changes = []): void
    {
        PermissionLog::create([
            'actor_user_id' => auth()->id(),
            'target_user_id' => $permission->target_user_id,
            'device_id' => $permission->device_id,
            'action' => $action,
            'changes' => $changes,
            'logged_at' => now(),
        ]);

        $this->systemLogService->log(
            eventType: "permission.{$action}",
            severity: 'info',
            actor: auth()->user(),
            message: "Permission {$action} for user {$permission->target_user_id} on device {$permission->device_id}",
            context: $changes,
        );
    }
}
