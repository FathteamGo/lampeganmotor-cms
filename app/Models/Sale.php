<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'customer_id',
        'user_id',
        'sale_date',
        'sale_price',
        'payment_method',
        'remaining_payment',
        'due_date',
        'cmo',
        'cmo_fee',
        'direct_commission',
        'order_source',
        'branch_name',
        'result',
        'status',
        'notes',
        'dp_po',
        'dp_real',
    ];

    protected $casts = [
        'sale_date'         => 'date',
        'due_date'          => 'date',
        'sale_price'        => 'decimal:2',
        'remaining_payment' => 'decimal:2',
        'cmo_fee'           => 'decimal:2',
        'direct_commission' => 'decimal:2',
        'dp_po'             => 'decimal:2',
        'dp_real'           => 'decimal:2',
    ];

    protected $appends = [
        'pencairan',
        'laba_bersih',
    ];

    // =======================
    // RELASI
    // =======================
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function incomes()
    {
        return $this->hasMany(Income::class, 'sale_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'sale_id');
    }

    protected function categoryId(string $slug, string $type): ?int
    {
        return DB::table('categories')
            ->where('name', $slug)
            ->where('type', $type)
            ->value('id');
    }

    // =======================
    // APPEND ATTRIBUTES
    // =======================
    public function getPencairanAttribute()
    {
        $catId = $this->categoryId('pencairan', 'income');

        if ($catId) {
            $sum = (float) $this->incomes()->where('category_id', $catId)->sum('amount');
            if ($sum > 0) {
                return $sum;
            }
        }

        return $this->payment_method === 'cash_tempo'
            ? (float) ($this->remaining_payment ?? 0)
            : (float) ($this->sale_price ?? 0);
    }

    public function getLabaBersihAttribute()
    {
        $purchase = (float) optional($this->vehicle)->purchase_price;
        $cmo      = (float) ($this->cmo_fee ?? 0);
        $komisi   = (float) ($this->direct_commission ?? 0);
        $dpPo     = (float) ($this->dp_po ?? 0);
        $dpReal   = (float) ($this->dp_real ?? 0);

        return (float) ($this->vehicle->sale_price ?? $this->sale_price ?? 0)
            - $dpPo
            + $dpReal
            - $purchase
            - $cmo
            - $komisi;
    }

    // =======================
    // MODEL EVENTS UNTUK AUTO UPDATE STATUS MOTOR
    // =======================
    protected static function booted()
    {
        // Saat sale dibuat, ubah status motor jadi 'sold'
        static::created(function ($sale) {
            if ($sale->vehicle && $sale->vehicle->status !== 'sold') {
                $sale->vehicle->update(['status' => 'sold']);
            }
        });

        // Opsional: jika sale dihapus, rollback status motor ke 'available'
        static::deleted(function ($sale) {
            if ($sale->vehicle && $sale->vehicle->status === 'sold') {
                $sale->vehicle->update(['status' => 'available']);
            }
        });
    }
}
