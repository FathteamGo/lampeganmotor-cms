<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_model_id',
        'type_id',
        'color_id',
        'year_id',
        'vin',
        'license_plate',
        'engine_number',
        'bpkb_number',
        'purchase_price',
        'sale_price',
        'odometer',
        'status',
        'description',
        'dp_percentage',
        'engine_specification',
        'notes',
        'location',
        'down_payment',
    ];

    protected $casts = [
        'views' => 'integer',
    ];

    // =======================
    // 🔗 RELASI
    // =======================

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

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function photos()
    {
        return $this->hasMany(VehiclePhoto::class)
            ->select(['id', 'vehicle_id', 'path', 'caption'])
            ->latest();
    }

    public function additionalCosts()
    {
        return $this->hasMany(AdditionalCost::class);
    }

    public function purchaseadditionalCosts()
    {
        return $this->hasManyThrough(
            PurchaseAdditionalCost::class,
            Purchase::class,
            'vehicle_id',               // Foreign key on purchases table
            'purchase_id',              // Foreign key on purchase_additional_costs table
            'id',                       // Local key on vehicles table
            'id'                        // Local key on purchases table
        );
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the latest truly active sale (proses/kirim only)
     * Sale 'selesai' tidak dianggap active (memungkinkan re-sell)
     */
    public function activeSale()
    {
        return $this->hasOne(Sale::class)
            ->whereIn('status', ['proses', 'kirim'])
            ->latest('id');
    }

    /**
     * Scope for truly available vehicles (no active sale - proses/kirim)
     * Motor dengan sale 'selesai' tetap bisa dijual lagi (re-sell)
     */
    public function scopeAvailableUnits($query)
    {
        return $query->whereDoesntHave('activeSale');
    }

    // =======================
    // ACCESSOR
    // =======================

   public function displayName(): Attribute
{
    return Attribute::make(
        get: function () {
            $brand = trim($this->vehicleModel?->brand?->name ?? '');
            $model = trim($this->vehicleModel?->name ?? 'Unknown');

            // Kalau nama model sudah mengandung brand (misal "Honda Beat"), jangan dobel
            if ($brand && str_starts_with(strtolower($model), strtolower($brand))) {
                return $model;
            }

            return trim("{$brand} {$model}");
        }
    );
}

}
