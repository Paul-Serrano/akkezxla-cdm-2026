<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'alias', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'user';

    const ROLE_ADMIN   = 'admin';
    const ROLE_WINAMAX = 'winamax';
    const ROLE_REGULAR = 'regular';

    public static array $roles = [
        self::ROLE_ADMIN   => 'Admin',
        self::ROLE_WINAMAX => 'Winamax',
        self::ROLE_REGULAR => 'Regular',
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isWinamax(): bool
    {
        return $this->role === self::ROLE_WINAMAX;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
