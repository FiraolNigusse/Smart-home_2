<?php

namespace App\Services;

use App\Models\Device;
use App\Models\User;
use App\Models\DevicePermission;
use App\Models\PermissionLog;
use Illuminate\Support\Carbon;

class AccessDecisionService
{
    public function __construct(
        protected RulesEngine $rulesEngine,
        protected AttributePolicyService $attributePolicyService,
    ) {
    }

    public function evaluate(User $user, Device $device, string $action, array $context = []): array
    {
        // Mandatory Access Control (MAC)
        $deviceSensitivity = $device->sensitivityLevel?->hierarchy ?? 0;
        $userClearance = $user->clearanceHierarchy();

        if ($deviceSensitivity > $userClearance) {
            return $this->deny('Access denied: insufficient clearance for this classification (MAC).');
        }

        // Role-based hierarchy check (RBAC baseline)
        $roleHierarchy = $user->role?->hierarchy ?? 0;
        if ($roleHierarchy < $device->min_role_hierarchy) {
            // Check DAC override
            $dacDecision = $this->evaluateDac($user, $device, $action);
            if (!$dacDecision['allowed']) {
                return $dacDecision;
            }
        }

        $context = array_merge([
            'location' => $context['location'] ?? $device->location,
            'device_type' => $device->type,
            'attributes' => $user->attributeProfile?->attributes ?? [],
        ], $context);

        // Attribute-based policies
        $abacDecision = $this->attributePolicyService->evaluate($user, $action, $context);
        if ($abacDecision['matched'] ?? false) {
            if (($abacDecision['effect'] ?? 'deny') === 'deny') {
                return $this->deny("Denied by ABAC policy: {$abacDecision['policy']}");
            }
        }

        // Rule-based (time/location/device rules)
        $ruleDecision = $this->rulesEngine->checkPermission($user, $device, $action, $context);
        if (!$ruleDecision['allowed']) {
            return $ruleDecision;
        }

        return [
            'allowed' => true,
            'message' => null,
            'rule' => $ruleDecision['rule'] ?? null,
        ];
    }

    protected function evaluateDac(User $user, Device $device, string $action): array
    {
        /** @var DevicePermission|null $permission */
        $permission = $device->permissions()
            ->where('target_user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', Carbon::now());
            })
            ->first();

        if (!$permission) {
            return $this->deny('Access denied: no discretionary permission granted.');
        }

        $allowedActions = $permission->allowed_actions ?? [];
        $canControl = $permission->can_control;

        if ($canControl || in_array($action, $allowedActions, true)) {
            PermissionLog::create([
                'actor_user_id' => $user->id,
                'target_user_id' => $user->id,
                'device_id' => $device->id,
                'action' => 'dac_override',
                'changes' => ['action' => $action],
                'logged_at' => now(),
            ]);

            return [
                'allowed' => true,
                'message' => null,
                'rule' => null,
            ];
        }

        return $this->deny('Access denied: action not permitted by DAC grant.');
    }

    protected function deny(string $message): array
    {
        return [
            'allowed' => false,
            'message' => $message,
            'rule' => null,
        ];
    }
}

