<?php

return [
    'berkunci' => [
        'POST api/v1/checkout',
        'POST api/v1/checkout/{kode}/batal',
        'POST api/v1/pesanan/{nomor}/terima',
        'POST api/v1/pesanan/{nomor}/batal',
    ],

    'tanpa_kunci' => [
        'POST api/v1/simulasi-belanja' => 'Hanya menghitung, tidak menulis.',
    ],
];
