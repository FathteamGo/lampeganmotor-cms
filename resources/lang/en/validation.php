<?php

return [

    'custom' => [
        'license_plate' => [
            'required' => 'License Plate is required!',
            'unique' => 'License Plate is already registered!',
        ],
        'atas_nama_stnk' => [
            'required' => 'STNK Owner Name is required!',
        ],
        'customer_id' => [
            'required' => 'Customer must be selected!',
        ],
    ],

    'attributes' => [
        'tgl' => 'Date',
        'license_plate' => 'License Plate',
        'atas_nama_stnk' => 'STNK Owner Name',
        'customer_id' => 'Customer',
        'total_pajak_jasa' => 'Total Tax + Fee',
        'dp' => 'Down Payment',
        'pembayaran_ke_samsat' => 'Payment to Samsat',
        'sisa_pembayaran' => 'Remaining Payment',
        'margin_total' => 'Margin',
        'diambil_tgl' => 'Taken Date',
        'status' => 'Status',
    ],
];
