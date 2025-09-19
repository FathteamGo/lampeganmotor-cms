<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeeklyReport extends Model
{
    use HasFactory;

    protected $table = 'weekly_reports'; 
    protected $fillable = [
        'start_date',
        'end_date',
        'visitors',
        'sales_count',
        'sales_total',
        'income_total',
        'expense_total',
        'total_income',
        'stock',
        'stnk_renewal',
        'top_motors',
        'insight',
        'read',
    ];

    protected $casts = [
        'top_motors' => 'array',
        'read' => 'boolean',
    ];
}
