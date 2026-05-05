<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'user';

    protected $fillable = ['name', 'email', 'alias', 'password'];

    const ROLE_ADMIN   = 'admin';
    const ROLE_REGULAR = 'regular';
    const ROLE_AKKEZXLA = 'akkezxla';
    const ROLE_USPEG = 'uspeg';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isAkkezxla(): bool
    {
        return $this->hasRole(self::ROLE_AKKEZXLA);
    }

    public function isUspeg(): bool
    {
        return $this->hasRole(self::ROLE_USPEG);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
