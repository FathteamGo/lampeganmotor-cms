<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['customer_id', 'vehicle_id', 'sale_date', 'price', 'marketing_user_id']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\SaleFactory> */
    use HasFactory;
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function marketingUser()
    {
        return $this->belongsTo(User::class, 'marketing_user_id');
    }
}
