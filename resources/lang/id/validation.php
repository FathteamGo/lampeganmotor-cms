<?php

return [

    'required' => 'Field ini wajib diisi.',

    'custom' => [
        'license_plate' => [
            'required' => 'Nomor Polisi wajib diisi!',
            'unique' => 'Nomor Polisi sudah terdaftar!',
        ],
        'atas_nama_stnk' => [
            'required' => 'Atas Nama STNK wajib diisi!',
        ],
        'customer_id' => [
            'required' => 'Customer wajib dipilih!',
        ],
    ],

    'attributes' => [
        'tgl' => 'Tanggal',
        'license_plate' => 'Nomor Polisi',
        'atas_nama_stnk' => 'Atas Nama STNK',
        'customer_id' => 'Customer',
        'total_pajak_jasa' => 'Total Pajak + Jasa',
        'dp' => 'DP',
        'pembayaran_ke_samsat' => 'Pembayaran ke Samsat',
        'sisa_pembayaran' => 'Sisa Pembayaran',
        'margin_total' => 'Margin',
        'diambil_tgl' => 'Tanggal Diambil',
        'status' => 'Status',
    ],
];
