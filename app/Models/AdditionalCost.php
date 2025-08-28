<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalCost extends Model
{
    protected $fillable = ['vehicle_id', 'description', 'amount', 'cost_date'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
