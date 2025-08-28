<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    protected $fillable = ['name', 'manufacturer', 'year']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\VehicleModelFactory> */
    use HasFactory;
}
