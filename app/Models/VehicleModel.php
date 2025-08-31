<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    protected $fillable = ['name', 'brand_id']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\VehicleModelFactory> */
    use HasFactory;
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
