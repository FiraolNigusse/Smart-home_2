<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevicePermission extends Model
{
    protected $fillable = [
        'device_id',
        'owner_user_id',
        'target_user_id',
        'can_view',
        'can_control',
        'allowed_actions',
        'expires_at',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_control' => 'boolean',
        'allowed_actions' => 'array',
        'expires_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
