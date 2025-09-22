<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    // Nama tabel (opsional, karena defaultnya plural dari model = visitors)
    protected $table = 'visitors';

    // Primary key (default sudah `id`, jadi optional juga)
    protected $primaryKey = 'id';

    // Kalau tidak ada kolom created_at & updated_at, matikan timestamps
    public $timestamps = false;

    // Kolom yang bisa diisi mass assignment
    protected $fillable = [
        'ip_address',
        'user_agent',
        'url',
        'visited_at',
    ];

    // Kalau butuh casting visited_at ke Carbon
    protected $casts = [
        'visited_at' => 'datetime',
    ];
}
