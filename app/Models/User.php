<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function author(): HasOne
    {
        return $this->hasOne(Author::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canBypassMaintenance();
    }

    public function canBypassMaintenance(): bool
    {
        return $this->hasAnyRole([
            UserRole::SuperAdmin->value,
            UserRole::Editor->value,
            UserRole::Author->value,
        ]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SuperAdmin->value);
    }

    public function isEditor(): bool
    {
        return $this->hasRole(UserRole::Editor->value);
    }

    public function isAuthor(): bool
    {
        return $this->hasRole(UserRole::Author->value);
    }
}
