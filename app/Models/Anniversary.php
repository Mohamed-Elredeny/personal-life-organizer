<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anniversary extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'title', 'description', 'date', 'type', 'is_recurring', 'is_shared',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_shared' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function nextOccurrence(): \Carbon\Carbon
    {
        if (! $this->is_recurring) {
            return $this->date;
        }
        $next = $this->date->copy()->setYear(now()->year);
        if ($next->isPast()) {
            $next->addYear();
        }
        return $next;
    }

    public function daysUntilNext(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->nextOccurrence(), false);
    }
}
