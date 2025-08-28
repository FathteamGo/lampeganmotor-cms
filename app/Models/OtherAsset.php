<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherAsset extends Model
{
    protected $fillable = ['name', 'description', 'value', 'acquisition_date']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\OtherAssetFactory> */
    use HasFactory;
}
