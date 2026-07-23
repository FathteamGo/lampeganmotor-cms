<?php
namespace App\Models;

use App\Models\Sale;
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

    // REMOVED: booted() event yang salah — Purchase save TIDAK boleh
    // mengubah vehicle status. Status hanya dikelola oleh:
    // - Sale::syncVehicleStatus() (set 'sold' saat ada active sale)
    // - CreatePurchase page (set 'available' untuk buyback)
    // - VehicleForm (manual edit dengan proteksi)
}

