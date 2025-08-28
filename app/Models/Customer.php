<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;
}
