<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cmo extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'notes',
    ];


    public function sales()
{
    return $this->hasMany(Sale::class);
}
}