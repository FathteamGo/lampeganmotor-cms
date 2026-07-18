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
        'dp_po', 'dp_real',
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
     * Logic (Correct):
     * - Jika ada 1+ sale dengan status 'proses'/'kirim' → vehicle status = 'sold'
     * - Jika semua sale 'selesai'/'cancel' atau tidak ada sale → vehicle status TETAP 'sold'
     * - Hanya buyback (CreatePurchase) yang mengubah status ke 'available'
     * - Motor yang sudah dijual ke customer tetap 'sold' meskipun sale selesai
     */
    public static function syncVehicleStatus($vehicleId)
    {
        try {
            $vehicle = Vehicle::find($vehicleId);
            if (!$vehicle) {
                return;
            }

            // Hanya hitung sale yang benar-benar aktif (proses/kirim)
            $trulyActiveCount = Sale::where('vehicle_id', $vehicleId)
                ->whereIn('status', ['proses', 'kirim'])
                ->count();

            // Tentukan status:
            // - Ada sale aktif (proses/kirim) → sold
            // - Tidak ada sale aktif → TETAP seperti sekarang (jangan otomatis available)
            //   Hanya buyback yang boleh set available
            if ($trulyActiveCount >= 1) {
                $newStatus = 'sold';
            } else {
                // Jangan ubah status - biarkan apa adanya
                // Vehicle yang sudah 'sold' tetap 'sold' meskipun sale selesai
                return;
            }

            // Update hanya jika status berbeda
            if ($vehicle->status !== $newStatus) {
                $vehicle->update(['status' => $newStatus]);
            }
        } catch (\Exception $e) {
            // Log error tapi jangan throw - hindari transaction failure
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
