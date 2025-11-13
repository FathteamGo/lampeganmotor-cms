<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StnkRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'tgl',
        'license_plate',
        'atas_nama_stnk',
        'customer_id',
        'total_pajak_jasa',
        'pembayaran_ke_samsat',
        'vendor',
        'payvendor',
        'foto_stnk',
        'jenis_pekerjaan',
        'dp',
        'sisa_pembayaran',
        'margin_total',
        'diambil_tgl',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Accessor untuk nomor telepon customer
    public function getNoTeleponAttribute(): ?string
    {
        return $this->customer?->phone;
    }
}
