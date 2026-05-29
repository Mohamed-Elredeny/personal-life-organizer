<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'assigned_to', 'project_id', 'goal_id', 'milestone_id', 'parent_task_id', 'sector_id',
        'title', 'description', 'status', 'priority',
        'due_date', 'completed_at', 'estimated_minutes', 'actual_minutes', 'is_shared',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'is_shared' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            if ($task->isDirty('status')) {
                $task->completed_at = $task->status === 'done' ? ($task->completed_at ?? now()) : null;
            }
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function goal(): BelongsTo { return $this->belongsTo(Goal::class); }
    public function milestone(): BelongsTo { return $this->belongsTo(Milestone::class); }
    public function sector(): BelongsTo { return $this->belongsTo(Sector::class); }
    public function parent(): BelongsTo { return $this->belongsTo(Task::class, 'parent_task_id'); }
    public function subtasks(): HasMany { return $this->hasMany(Task::class, 'parent_task_id'); }
}
