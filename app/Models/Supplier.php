<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'dealer',
        'phone',
        'address'
    ];

    /**
     * Relasi ke Request (permintaan motor)
     */
    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    /**
     * Relasi ke Purchase (pembelian motor)
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Ambil semua vehicles lewat purchases
     * (karena purchase punya vehicle_id)
     */
    public function vehicles()
    {
        return $this->hasManyThrough(
            Vehicle::class,
            Purchase::class,
            'supplier_id',  // Foreign key di purchases table
            'id',           // Foreign key di vehicles table
            'id',           // Local key di suppliers table
            'vehicle_id'    // Local key di purchases table
        );
    }
}