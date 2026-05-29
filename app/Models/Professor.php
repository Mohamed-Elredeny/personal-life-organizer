<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Professor extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'goal_id', 'scholarship_id',
        'name', 'title', 'university', 'country', 'email', 'lab', 'research_area',
        'website', 'status', 'priority', 'last_contact_at', 'next_follow_up_at',
        'notes', 'is_shared',
    ];

    protected $casts = [
        'last_contact_at' => 'date',
        'next_follow_up_at' => 'date',
        'is_shared' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function goal(): BelongsTo { return $this->belongsTo(Goal::class); }
    public function scholarship(): BelongsTo { return $this->belongsTo(Scholarship::class); }

    public function daysUntilFollowUp(): ?int
    {
        if (! $this->next_follow_up_at) return null;
        return (int) now()->startOfDay()->diffInDays($this->next_follow_up_at, false);
    }
}
