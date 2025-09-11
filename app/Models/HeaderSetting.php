<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeaderSetting extends Model
{
    protected $fillable = [
        'site_name', 'logo', 'instagram_url', 'tiktok_url'
    ];

    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return asset('images/no-image.png');
        }

        return Storage::url(str_replace('public/', '', $this->logo));
    }
}

