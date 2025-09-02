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

    // Cast purchase_date ke datetime
    protected $casts = [
        'purchase_date' => 'datetime',
    ];

    // Relasi ke VehicleModel
    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class);
    }

    // accessor supaya bisa dipakai di Select / Table
    public function getNameAttribute()
    {
        return $this->vehicleModel->name ?? 'Unknown';
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Relasi ke Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
    