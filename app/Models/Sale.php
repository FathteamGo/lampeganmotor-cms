<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'customer_id', 'user_id',
        'sale_date', 'sale_price', 'payment_method',
        'leasing', 'remaining_payment', 'due_date', 'cmo',
        'cmo_fee', 'direct_commission', 'order_source',
        'branch_name', 'result', 'status', 'notes',
        'dp_po', 'dp_real', 'payment_to_customer',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'due_date' => 'date',
        'sale_price' => 'decimal:2',
        'remaining_payment' => 'decimal:2',
        'cmo_fee' => 'decimal:2',
        'direct_commission' => 'decimal:2',
        'dp_po' => 'decimal:2',
        'dp_real' => 'decimal:2',
        'payment_to_customer' => 'decimal:2',
    ];

    protected $appends = ['pencairan', 'harga_total_penjualan', 'laba_bersih'];

    // =======================
    // RELASI
    // =======================
    public function customer() { return $this->belongsTo(Customer::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function incomes() { return $this->hasMany(Income::class, 'sale_id'); }
    public function expenses() { return $this->hasMany(Expense::class, 'sale_id'); }
    public function purchase() { return $this->hasOne(Purchase::class, 'vehicle_id', 'vehicle_id'); }

    // =======================
    // SCOPES
    // =======================
    public function scopeValid($query)
    {
        return $query->where('status', '!=', 'cancel');
    }

    // =======================
    // APPEND ATTRIBUTES
    // =======================
    protected function categoryId(string $slug, string $type): ?int
    {
        return DB::table('categories')
            ->where('name', $slug)
            ->where('type', $type)
            ->value('id');
    }

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

    /**
     * Harga Total Penjualan (Revenue Dealer)
     *
     * Rumus Bos Iqbal (DIPERBARUI + FIX DANA_TUNAI):
     * - CREDIT: HTP = OTR - DP PO + DP REAL
     * - CASH / CASH_TEMPO / DANA_TUNAI / TUKARTAMBAH: HTP = OTR
     *
     * FIX: Sebelumnya cek $dpPo > 0 (salah — Dana Tunai juga bisa ada DP PO),
     *      sekarang cek payment_method === 'credit' secara eksplisit.
     */
    public function getHargaTotalPenjualanAttribute(): float
    {
        $otr = (float) ($this->sale_price ?? 0);

        // Hanya credit yang pakai formula: HTP = OTR - DP PO + DP REAL
        if ($this->payment_method === 'credit') {
            $dpPo = (float) ($this->dp_po ?? 0);
            $dpReal = (float) ($this->dp_real ?? 0);
            return $otr - $dpPo + $dpReal;
        }

        // Cash, Cash Tempo, Dana Tunai, Tukar Tambah: HTP = OTR
        return $otr;
    }

    /**
     * Laba Kotor = Harga Total Penjualan - Harga Total Pembelian
     *
     * Rumus Bos Iqbal:
     * LABA KOTOR = HARGA TOTAL PENJUALAN - HARGA TOTAL PEMBELIAN
     */
    public function getLabaKotorAttribute()
    {
        $hargaTotalPenjualan = $this->harga_total_penjualan;

        $purchase = $this->purchase;
        $hargaTotalPembelian = $purchase ? (float) $purchase->grand_total : 0;
        if ($hargaTotalPembelian == 0) {
            $hargaTotalPembelian = (float) optional($this->vehicle)->purchase_price;
        }

        return $hargaTotalPenjualan - $hargaTotalPembelian;
    }

    /**
     * Laba Bersih = Laba Kotor - CMO - Sales (Komisi Langsung)
     *
     * Rumus Bos Iqbal:
     * LABA BERSIH = KEUNTUNGAN - CMO - SALES
     */
    public function getLabaBersihAttribute()
    {
        $laba = $this->laba_kotor;
        $cmo = (float) ($this->cmo_fee ?? 0);
        $komisi = (float) ($this->direct_commission ?? 0);

        return $laba - ($cmo + $komisi);
    }


    // =======================
    // MODEL EVENTS
    // =======================
    protected static function booted()
    {
        static::created(function ($sale) {
            self::syncVehicleStatus($sale->vehicle_id);
        });

        static::updated(function ($sale) {
            self::syncVehicleStatus($sale->vehicle_id);
        });

        static::deleted(function ($sale) {
            self::syncVehicleStatus($sale->vehicle_id);
        });
    }

    /**
     * Sinkronisasi status vehicle berdasarkan sales records
     *
     * Logic:
     * - Sale 'proses'/'kirim'/'selesai' → vehicle status = 'sold'
     *   (selesai = motor sudah dijual & dikirim ke customer, tetap sold)
     * - Semua sale 'cancel' atau tidak ada sale → TETAP seperti sekarang
     *   (jangan otomatis available, hanya buyback yang boleh set available)
     */
    public static function syncVehicleStatus($vehicleId)
    {
        try {
            $vehicle = Vehicle::find($vehicleId);
            if (!$vehicle) {
                return;
            }

            // Hitung sale yang menandakan motor sudah terjual
            // proses = sedang proses, kirim = sedang dikirim, selesai = sudah sampai customer
            $soldCount = Sale::where('vehicle_id', $vehicleId)
                ->whereIn('status', ['proses', 'kirim', 'selesai'])
                ->count();

            if ($soldCount >= 1) {
                $newStatus = 'sold';
            } else {
                // Semua sale cancel atau tidak ada sale
                // Jangan ubah status - biarkan apa adanya
                // Hanya buyback (CreatePurchase) yang boleh set available
                return;
            }

            // Update hanya jika status berbeda
            if ($vehicle->status !== $newStatus) {
                $vehicle->update(['status' => $newStatus]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync vehicle status for vehicle_id: {$vehicleId}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function cmo()
{
    return $this->belongsTo(Cmo::class);
}

}
