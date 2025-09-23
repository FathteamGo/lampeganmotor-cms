<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = ['category_id', 'description', 'amount', 'income_date']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\IncomeFactory> */
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
