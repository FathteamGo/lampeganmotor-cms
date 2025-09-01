<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'supplier_id',
        'purchase_date',
        'total_price',
        'notes',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
