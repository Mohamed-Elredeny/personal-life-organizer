<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scholarship extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'goal_id', 'name', 'university', 'country', 'level', 'status',
        'deadline', 'amount', 'currency', 'funding_type', 'url', 'notes', 'is_shared',
    ];

    protected $casts = [
        'deadline' => 'date',
        'amount' => 'decimal:2',
        'is_shared' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function goal(): BelongsTo { return $this->belongsTo(Goal::class); }
    public function professors(): HasMany { return $this->hasMany(Professor::class); }

    public function daysUntilDeadline(): ?int
    {
        if (! $this->deadline) return null;
        return (int) now()->startOfDay()->diffInDays($this->deadline, false);
    }
}
