<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SensitivityLevel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'hierarchy',
        'is_system_defined',
    ];

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
