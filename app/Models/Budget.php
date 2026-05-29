<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use BelongsToOwner;

    protected $fillable = [
        'user_id', 'category_id', 'name', 'amount', 'currency',
        'period', 'starts_on', 'is_shared',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'starts_on' => 'date',
        'is_shared' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
}
