<?php

namespace App\Models;

use App\Models\MasterData\UserCategory;
use Filament\Models\Contracts\FilamentUser ;
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
use App\Models\Team; // Tambah import ini (asumsi Team di App\Models)
use App\Models\WhatsAppNumber; // Tambah import ini

/**
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(string|array $roles)
 * @method bool can(string $permission)
 */
class User extends Authenticatable // Pastikan extend Authenticatable (yang inherit Model)
{
    use HasFactory, Notifiable, HasRoles; // Tambah HasRoles ke use (kalau belum)

    // Hapus HasApiTokens kalau gak dipakai, atau tambah ke use kalau perlu
    // use HasApiTokens; // Uncomment kalau butuh API tokens

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'base_salary',
        'bonus',
        'image', // Tambah ini kalau dipakai di getFilamentAvatarUrl()
        'user_category_id', // Asumsi field ini ada untuk relation
        'hide_insight_modals', // Pastikan ada (dari migrasi sebelumnya)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Fix casts: Gabung semua ke property ini, HAPUS method casts()!
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'string',
        'hide_insight_modals' => 'boolean', // Fix: Tambah type boolean (penting untuk modal logic)
    ];

    public function customers()
{
    return $this->hasMany(Customer::class, 'cmo_id');
}

    // HAPUS method ini: protected function casts(): array { ... } – konflik dengan property!

    public function userCategory(): BelongsTo
    {
        return $this->belongsTo(UserCategory::class); // Fix: Capitalize class name
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
        return Team::all(); // Sekarang imported
    }

    public function isCategory(string $category): bool
    {
        return $this->userCategory?->name === $category; // Tambah null-safe (?->) biar aman kalau relation null
    }

    public function isCategoryId(int $id): bool
    {
        return $this->user_category_id === $id;
    }

    public function coaches(): HasMany // Tambah return type
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

    public function canDeleteUser (): bool
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

    public function coach(): HasOne // Tambah return type
    {
        return $this->hasOne(\App\Models\MasterData\Coach::class, 'user_id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        // Kalau user punya foto profil simpan di field `photo` atau `image`
        return $this->image
            ? Storage::url($this->image)   // misal storage/app/public/photos
            : null; // fallback ke avatar default (initial)
    }

    public function whatsAppNumbers(): HasMany // Tambah return type
    {
        return $this->hasMany(WhatsAppNumber::class); // Sekarang imported
    }

    // Tambah override ini untuk fix Intelephense "Undefined method 'update'"
    public function update(array $attributes = [], array $options = []): bool
    {
        return parent::update($attributes, $options);
    }
}