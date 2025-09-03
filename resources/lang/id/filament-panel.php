<?php

return [
    'actions' => [
        'logout' => [
            'label' => 'Keluar',
        ],
    ],

    'avatar' => [
        'alt' => 'Avatar untuk :name',
    ],

    'breadcrumbs' => [
        'actions' => [
            'toggle_sidebar' => 'Toggle sidebar',
        ],
    ],

    'global_search' => [
        'actions' => [
            'open' => 'Buka pencarian global',
        ],

        'field' => [
            'placeholder' => 'Cari...',
        ],

        'indicator' => 'Buka pencarian global',

        'results' => [
            'empty' => [
                'heading' => 'Tidak ada hasil',
                'description' => 'Coba dengan kata kunci yang berbeda.',
            ],

            'headings' => [
                'pages' => 'Halaman',
                'records' => 'Catatan',
            ],
        ],
    ],

    'layout' => [
        'actions' => [
            'close_sidebar' => 'Tutup sidebar',
            'open_sidebar' => 'Buka sidebar',
            'sidebar_collapse' => 'Lipat sidebar',
            'sidebar_expand' => 'Perluas sidebar',
        ],

        'direction' => 'ltr',
    ],

    'login' => [
        'actions' => [
            'authenticate' => [
                'label' => 'Masuk',
            ],

            'request_password_reset' => [
                'label' => 'Lupa kata sandi?',
            ],
        ],

        'fields' => [
            'email' => [
                'label' => 'Alamat email',
            ],

            'password' => [
                'label' => 'Kata sandi',
            ],

            'remember' => [
                'label' => 'Ingat saya',
            ],
        ],

        'heading' => 'Masuk ke akun Anda',

        'messages' => [
            'failed' => 'Kredensial tidak cocok dengan catatan kami.',
        ],

        'notifications' => [
            'throttled' => [
                'title' => 'Terlalu banyak percobaan masuk',
                'body' => 'Silakan coba lagi dalam :seconds detik.',
            ],
        ],
    ],

    'pages' => [
        'health_check' => [
            'heading' => 'Pemeriksaan Kesehatan',

            'navigation' => [
                'label' => 'Pemeriksaan Kesehatan',
            ],

            'notifications' => [
                'check_results' => 'Hasil pemeriksaan dari',
            ],
        ],
    ],

    'user_menu' => [
        'actions' => [
            'logout' => [
                'label' => 'Keluar',
            ],

            'open_user_menu' => 'Buka menu pengguna',
        ],

        'heading' => 'Menu Pengguna',
    ],

    'widgets' => [
        'account' => [
            'actions' => [
                'open' => 'Buka widget akun',
            ],

            'heading' => 'Selamat datang kembali, :name',
        ],

        'filament_info' => [
            'actions' => [
                'open' => 'Buka widget info Filament',
            ],

            'heading' => 'Info Filament',
        ],
    ],

    // Tambahan untuk date filter
    'filters' => [
        'start_date' => 'Tanggal Mulai',
        'end_date' => 'Tanggal Akhir',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'apply' => 'Terapkan',
        'clear' => 'Bersihkan',
    ],
];