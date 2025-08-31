<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- PASTIKAN USE STATEMENT INI ADA


class Vehicle extends Model
{
    protected $fillable = ['vehicle_model_id', 'type_id', 'color_id', 'year_id', 'vin', 'license_plate', 'engine_number', 'bpkb_number', 'purchase_price', 'sale_price', 'status', 'description', 'dp_percentage', 'engine_specification', 'notes', 'location']; // Agar bisa diisi massal
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

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() =>
            // Menggunakan optional() agar tidak error jika relasi kosong
            optional($this->vehicleModel->brand)->name . ' ' .
                optional($this->vehicleModel)->name
            // optional($this->vehicleModel)->name . ' - ' .
            // optional($this->year)->year
        );
    }
}
