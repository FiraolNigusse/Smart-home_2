<?php

namespace App\Services;

use App\Models\Rule;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Collection;

class RulesEngine
{
    /**
     * Check if an action is allowed based on rules.
     *
     * @param User $user
     * @param Device $device
     * @param string $action
     * @return array ['allowed' => bool, 'message' => string|null, 'rule' => Rule|null]
     */
    public function checkPermission(User $user, Device $device, string $action): array
    {
        // First check if user has minimum role hierarchy for device
        if (!$device->isAccessibleBy($user->role->hierarchy ?? 0)) {
            return [
                'allowed' => false,
                'message' => 'You do not have permission to access this device.',
                'rule' => null,
            ];
        }

        // Get all applicable rules
        $applicableRules = $this->getApplicableRules($user, $device, $action);

        // Evaluate rules in order (deny rules take precedence)
        foreach ($applicableRules as $rule) {
            if ($rule->effect === 'deny' && $rule->evaluateCondition()) {
                return [
                    'allowed' => false,
                    'message' => $rule->denial_message ?? 'Action denied by system rule.',
                    'rule' => $rule,
                ];
            }
        }

        // Check if there's an explicit allow rule
        foreach ($applicableRules as $rule) {
            if ($rule->effect === 'allow' && $rule->evaluateCondition()) {
                return [
                    'allowed' => true,
                    'message' => null,
                    'rule' => $rule,
                ];
            }
        }

        // Default: allow if no deny rules matched
        return [
            'allowed' => true,
            'message' => null,
            'rule' => null,
        ];
    }

    /**
     * Get all rules applicable to the given context.
     *
     * @param User $user
     * @param Device $device
     * @param string $action
     * @return Collection
     */
    protected function getApplicableRules(User $user, Device $device, string $action): Collection
    {
        return Rule::where('is_active', true)
            ->get()
            ->filter(function ($rule) use ($user, $device, $action) {
                return $rule->appliesTo(
                    $user->role_id,
                    $device->id,
                    $action
                );
            });
    }
}


