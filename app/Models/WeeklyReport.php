<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyReport extends Model
{
    use HasFactory;

    protected $table = 'weekly_reports'; // Sesuaikan kalau nama table beda

    protected $fillable = [
        'start_date',
        'end_date',
        'visitors',
        'sales_count',
        'sales_total',
        'total_income', // Dari kode runSample, ini total_income
        'expense_total',
        'stock',
        'stnk_renewal',
        'top_motors',
        'insight',
        'read', // Field untuk unread modal
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'top_motors' => 'array',
        'read' => 'boolean', // Penting untuk modal logic
        'visitors' => 'integer',
        'sales_count' => 'integer',
        'sales_total' => 'decimal:2',
        'total_income' => 'decimal:2',
        'expense_total' => 'decimal:2',
        'stock' => 'integer',
        'stnk_renewal' => 'integer',
    ];

    // Override untuk fix Intelephense "Undefined method 'update'"
    public function update(array $attributes = [], array $options = []): bool
    {
        return parent::update($attributes, $options);
    }
}
