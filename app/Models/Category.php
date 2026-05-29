<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'name', 'type', 'color', 'icon', 'is_shared',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function budgets(): HasMany { return $this->hasMany(Budget::class); }
}
