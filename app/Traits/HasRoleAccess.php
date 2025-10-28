<?php

namespace App\Traits;

trait HasRoleAccess
{
    /**
     * Cek apakah user adalah Admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    /**
     * Cek apakah user adalah Owner
     */
    public function isOwner(): bool
    {
        return $this->hasRole('Owner');
    }

    /**r
     * Cek apakah user adalah Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Cek apakah user punya salah satu role
     */
    public function hasAny(array $roles): bool
    {
        return $this->hasAnyRole($roles);
    }
}
