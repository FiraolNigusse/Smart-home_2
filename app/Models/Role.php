<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'hierarchy',
        'sensitivity_level_id',
    ];

    /**
     * Get all users with this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all rules for this role.
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    public function sensitivityLevel(): BelongsTo
    {
        return $this->belongsTo(SensitivityLevel::class);
    }

    public function roleChangeRequests(): HasMany
    {
        return $this->hasMany(RoleChangeRequest::class, 'requested_role_id');
    }
}
