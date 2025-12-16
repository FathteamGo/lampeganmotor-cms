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

        public function additionalCosts()
    {
        return $this->hasMany(PurchaseAdditionalCost::class);
    }

    public function getGrandTotalAttribute()
    {
        $extra = $this->additionalCosts->sum('price');
        return $this->total_price + $extra;
    }

    public function photos()
    {
        return $this->hasManyThrough(
            VehiclePhoto::class,
            Vehicle::class,
            'id',           // Foreign key on vehicles table
            'vehicle_id',   // Foreign key on vehicle_photos table
            'vehicle_id',   // Local key on purchases table
            'id'            // Local key on vehicles table
        );
    }

    // Relasi ke Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

  protected static function booted()
    {
        static::saved(function ($purchase) {
            if ($purchase->vehicle && $purchase->vehicle->status === 'hold') {
                $purchase->vehicle->update(['status' => 'available']); // atau 'active'
            }
        });
    }
}

    