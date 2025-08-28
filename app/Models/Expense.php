<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['category_id', 'description', 'amount', 'expense_date']; // Agar bisa diisi massal
    /** @use HasFactory<\Database\Factories\ExpenseFactory> */
    use HasFactory;
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
