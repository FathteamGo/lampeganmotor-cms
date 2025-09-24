<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppNumber extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_numbers'; // pastikan ini sesuai
    protected $fillable = ['name', 'number', 'is_active', 'user_id', 'is_report_gateway'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function user()
{
    return $this->belongsTo(User::class);
}

}
