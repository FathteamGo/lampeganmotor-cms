<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'supplier_id','brand_id','vehicle_model_id','year_id',
        'odometer','type','status','notes','vehicle_id',
        'license_plate',
    ];

    public function supplier(){ return $this->belongsTo(\App\Models\Supplier::class); }
    public function brand(){ return $this->belongsTo(\App\Models\Brand::class); }
    public function vehicleModel(){ return $this->belongsTo(\App\Models\VehicleModel::class); }
    public function year(){ return $this->belongsTo(\App\Models\Year::class); }
    public function photos(){
        return $this->hasMany(\App\Models\VehiclePhoto::class)->whereNull('vehicle_id');
    }
}