<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    protected $fillable = ['year']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\YearFactory> */
    use HasFactory;
}
