<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;

class AttributePolicyService
{
    /**
     * Evaluate attribute-based policies for a given user and action.
     */
    public function evaluate(User $user, string $action, array $context = []): array
    {
        $policies = config('access.abac_policies', []);
        $attributes = $this->buildAttributeBag($user, $context);

        foreach ($policies as $policy) {
            if (!empty($policy['actions']) && !in_array($action, $policy['actions'], true)) {
                continue;
            }

            if ($this->matchesConditions($policy['conditions'] ?? [], $attributes)) {
                return [
                    'matched' => true,
                    'effect' => $policy['effect'] ?? 'deny',
                    'policy' => $policy['name'] ?? 'Unnamed ABAC Policy',
                ];
            }
        }

        return ['matched' => false];
    }

    protected function buildAttributeBag(User $user, array $context): array
    {
        $profile = $user->attributeProfile;

        $attributes = [
            'role' => $user->role?->slug,
            'role_hierarchy' => $user->role?->hierarchy,
            'department' => $profile?->department,
            'location' => $profile?->location,
            'employment_status' => $profile?->employment_status,
            'time_window' => Carbon::now()->format('H:i'),
        ];

        if ($profile && is_array($profile->attributes)) {
            $attributes = array_merge($attributes, $profile->attributes);
        }

        return array_merge($attributes, $context);
    }

    protected function matchesConditions(array $conditions, array $attributes): bool
    {
        foreach ($conditions as $condition) {
            $attribute = $condition['attribute'] ?? null;
            $operator = $condition['operator'] ?? 'equals';
            $value = $condition['value'] ?? null;

            $actual = $attributes[$attribute] ?? null;

            switch ($operator) {
                case 'equals':
                    if ($actual !== $value) {
                        return false;
                    }
                    break;
                case 'in':
                    if (!is_array($value) || !in_array($actual, $value, true)) {
                        return false;
                    }
                    break;
                case 'between':
                    if (!is_array($value) || count($value) < 2) {
                        return false;
                    }
                    if ($actual < $value[0] || $actual > $value[1]) {
                        return false;
                    }
                    break;
                case 'not_equals':
                    if ($actual === $value) {
                        return false;
                    }
                    break;
                default:
                    return false;
            }
        }

        return true;
    }
}

