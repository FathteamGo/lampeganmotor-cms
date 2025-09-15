<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nik',
        'phone',
        'address',
        'instagram',
        'tiktok',
    ];

    /* ----------------- Mutators ----------------- */
    public function setInstagramAttribute($value): void
    {
        $this->attributes['instagram'] = $this->cleanHandle($value);
    }

    public function setTiktokAttribute($value): void
    {
        $this->attributes['tiktok'] = $this->cleanHandle($value);
    }

    private function cleanHandle(?string $value): ?string
    {
        if (!$value) return null;
        $v = trim($value);

        // hapus @ di depan
        $v = ltrim($v, '@');

        // kalau URL, ambil username terakhir
        $v = preg_replace('~https?://(www\.)?(instagram\.com|tiktok\.com)/@?([^/?#]+).*~i', '$3', $v);

        return $v ?: null;
    }

    /* ----------------- Accessors ----------------- */
    public function getInstagramUrlAttribute(): ?string
    {
        return $this->instagram ? "https://instagram.com/{$this->instagram}" : null;
    }

    public function getTiktokUrlAttribute(): ?string
    {
        return $this->tiktok ? "https://tiktok.com/@{$this->tiktok}" : null;
    }

     public function stnkRenewals()
    {
        return $this->hasMany(StnkRenewal::class);
    }
}
