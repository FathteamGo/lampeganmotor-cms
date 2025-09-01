<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePhoto extends Model
{
    protected $fillable = ['request_id','vehicle_id','path','caption','photo_order'];
    public function request(){ return $this->belongsTo(\App\Models\Request::class); }
    public function vehicle(){ return $this->belongsTo(\App\Models\Vehicle::class); }
}