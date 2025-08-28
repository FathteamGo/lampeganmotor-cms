<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'description']; // Agar bisa diisi massal

    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;
}
