<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['vehicle_model_id', 'type_id', 'color_id', 'year', 'vin', 'license_plate', 'purchase_price', 'status']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory;

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class);
    }
    public function type()
    {
        return $this->belongsTo(Type::class);
    }
    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    public function photos()
    {
        return $this->hasMany(VehiclePhoto::class);
    }
    public function additionalCosts()
    {
        return $this->hasMany(AdditionalCost::class);
    }
    public function sale()
    {
        return $this->hasOne(Sale::class);
    }
}
