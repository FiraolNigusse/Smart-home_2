<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionLog extends Model
{
    protected $fillable = [
        'actor_user_id',
        'target_user_id',
        'device_id',
        'action',
        'changes',
        'notes',
        'logged_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'logged_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
