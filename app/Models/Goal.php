<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'title', 'description', 'category', 'horizon', 'status',
        'start_date', 'target_date', 'completed_at', 'progress', 'is_shared',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_date' => 'date',
        'completed_at' => 'date',
        'progress' => 'integer',
        'is_shared' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function milestones(): HasMany { return $this->hasMany(Milestone::class)->orderBy('sort'); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }

    public function recalculateProgress(): void
    {
        $total = $this->milestones()->count();
        if ($total === 0) {
            return;
        }
        $done = $this->milestones()->where('is_completed', true)->count();
        $this->progress = (int) round(($done / $total) * 100);
        if ($this->progress === 100 && $this->status !== 'completed') {
            $this->status = 'completed';
            $this->completed_at = now();
        }
        $this->saveQuietly();
    }
}
