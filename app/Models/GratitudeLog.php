<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GratitudeLog extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'log_date', 'content', 'mood', 'is_shared',
    ];

    protected $casts = [
        'log_date' => 'date',
        'mood' => 'integer',
        'is_shared' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
