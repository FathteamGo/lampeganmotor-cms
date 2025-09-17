<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Banner extends Model
{
    protected $fillable = [
        'title', 'image', 'start_date', 'end_date', 'is_active'
    ];

    public function scopeCurrentlyActive($query)
    {
        $today = Carbon::today();
        return $query->where('is_active', true)
                     ->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today);
    }
}

