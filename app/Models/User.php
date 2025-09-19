<?php

namespace App\Models;

use App\Models\MasterData\UserCategory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;



/**
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(string|array $roles)
 * @method bool can(string $permission)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
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
            'role' => 'string',
        ];
    }
    public function userCategory(): BelongsTo
    {
        return $this->belongsTo(userCategory::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return true;
    }

    public function getTenants(Panel $panel): Collection
    {
        return Team::all();
    }

    public function isCategory(string $category): bool
    {
        return $this->userCategory->name === $category;
    }

    public function isCategoryId(int $id): bool
    {
        return $this->user_category_id === $id;
    }

    public function coaches()
    {
        return $this->hasMany(\App\Models\MasterData\Coach::class);
    }
    public function syncRoleWithCategory(): void
    {
        if ($this->userCategory) {
            $this->syncRoles([]);
            $this->assignRole($this->userCategory->name);
        }
    }

    /**
     * Cek izin — Super Admin auto true
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        return $this->can($permission) || $this->hasPermissionTo($permission);
    }

    public function canDeleteUser(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function canDeleteResource(string $resource): bool
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        return $this->hasRole('Manager') && $resource !== 'settings';
    }

    public function coach()
    {
        return $this->hasOne(\App\Models\MasterData\Coach::class, 'user_id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        // Kalau user punya foto profil simpan di field `photo`
        return $this->image
            ? Storage::url($this->image)   // misal storage/app/public/photos
            : null; // fallback ke avatar default (initial)
    }
}
