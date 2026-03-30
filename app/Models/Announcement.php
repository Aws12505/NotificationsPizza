<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'type',
        'title',
        'body',
        'version',
        'is_active',
        'starts_at',
        'ends_at',
        'is_pinned',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_pinned' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function userStates(): HasMany
    {
        return $this->hasMany(AnnouncementUserState::class);
    }
}