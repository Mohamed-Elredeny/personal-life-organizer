<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToOwner
{
    protected static function bootBelongsToOwner(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->user_id)) {
                $model->user_id = Auth::id();
            }
        });

        static::addGlobalScope('owner', function (Builder $builder) {
            if (! Auth::check()) {
                return;
            }
            $userId = Auth::id();
            $table = $builder->getModel()->getTable();
            $builder->where(function (Builder $q) use ($userId, $table) {
                $q->where($table . '.user_id', $userId)
                    ->orWhere($table . '.is_shared', true);
            });
        });
    }
}
