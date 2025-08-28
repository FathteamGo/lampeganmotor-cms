<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['item', 'quantity', 'price', 'purchase_date']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\PurchaseFactory> */
    use HasFactory;
}
