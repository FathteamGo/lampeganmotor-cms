<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Vehicle extends Model
{
    use HasFactory;

    /** Status sale yang benar-benar mengunci motor (tidak termasuk 'selesai') */
    public const LOCKING_SALE_STATUSES = ['proses', 'kirim'];

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

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // =======================
    // SIKLUS JUAL-BELI
    // =======================

    /** Sale yang sedang berjalan untuk motor ini. $exceptSaleId untuk mode edit. */
    public function runningSale(?int $exceptSaleId = null): ?Sale
    {
        return $this->sales()
            ->whereIn('status', self::LOCKING_SALE_STATUSES)
            ->when($exceptSaleId, fn ($q) => $q->where('id', '!=', $exceptSaleId))
            ->latest('id')
            ->first();
    }

    public function isSellable(): bool
    {
        return $this->status === 'available' && $this->runningSale() === null;
    }

    /**
     * Purchase yang jadi modal untuk penjualan pada tanggal tertentu.
     *
     * Motor buyback punya lebih dari satu purchase, jadi yang dipakai adalah
     * pembelian terakhir sebelum (atau pada) tanggal jual. Kalau tidak ada yang
     * mendahului tanggal jual (misal sale di-backdate), pakai pembelian terbaru
     * — bukan yang tertua, karena itu harga beli dari siklus yang sudah lewat.
     */
    public function purchaseForSaleDate($saleDate = null): ?Purchase
    {
        if ($saleDate) {
            $purchase = $this->purchases()
                ->whereDate('purchase_date', '<=', \Illuminate\Support\Carbon::parse($saleDate)->toDateString())
                ->orderByDesc('purchase_date')
                ->orderByDesc('id')
                ->first();

            if ($purchase) {
                return $purchase;
            }
        }

        return $this->purchases()
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Get the latest truly active sale (proses/kirim only)
     * Sale 'selesai' tidak dianggap active (motor sudah di customer)
     */
    public function activeSale()
    {
        return $this->hasOne(Sale::class)
            ->whereIn('status', ['proses', 'kirim'])
            ->latest('id');
    }

    /**
     * Scope for truly available vehicles (stok real di showroom)
     * Hanya motor dengan status 'available' (stok awal + buyback)
     * Motor 'sold' tetap 'sold' meskipun sale sudah 'selesai' (masih di customer)
     */
    public function scopeAvailableUnits($query)
    {
        return $query->where('status', 'available');
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
