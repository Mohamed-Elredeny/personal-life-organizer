<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'role',
        'default_currency',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url
            ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    public function sectors(): HasMany { return $this->hasMany(Sector::class); }
    public function projects(): HasMany { return $this->hasMany(Project::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
    public function goals(): HasMany { return $this->hasMany(Goal::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function budgets(): HasMany { return $this->hasMany(Budget::class); }
    public function categories(): HasMany { return $this->hasMany(Category::class); }
    public function anniversaries(): HasMany { return $this->hasMany(Anniversary::class); }
    public function gratitudeLogs(): HasMany { return $this->hasMany(GratitudeLog::class); }
    public function scholarships(): HasMany { return $this->hasMany(Scholarship::class); }
    public function professors(): HasMany { return $this->hasMany(Professor::class); }
}
