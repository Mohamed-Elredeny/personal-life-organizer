<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'sector_id', 'name', 'description',
        'status', 'priority', 'start_date', 'due_date', 'completed_at',
        'progress', 'is_shared',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'date',
        'progress' => 'integer',
        'is_shared' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function sector(): BelongsTo { return $this->belongsTo(Sector::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
}
