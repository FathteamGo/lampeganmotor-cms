<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 *
 * @method bool hasRole(string|array|\Spatie\Permission\Models\Role ...$roles)
 * @method bool hasAnyRole(string|array|\Spatie\Permission\Models\Role ...$roles)
 * @method bool hasAllRoles(string|array|\Spatie\Permission\Models\Role ...$roles)
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany roles()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany permissions()
 */

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
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
}
