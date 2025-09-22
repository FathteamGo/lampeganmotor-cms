<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModalDismiss extends Model
{
    use HasFactory;

    protected $table = 'user_modal_dismiss';

    protected $fillable = [
        'user_id',
        'modal_key',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
