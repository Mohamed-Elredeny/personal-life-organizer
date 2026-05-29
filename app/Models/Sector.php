<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'name', 'color', 'icon', 'description', 'is_shared', 'is_archived',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function projects(): HasMany { return $this->hasMany(Project::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
}
