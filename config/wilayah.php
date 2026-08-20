<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Data Wilayah
|--------------------------------------------------------------------------
| Data referensi statis untuk peta & analitik sebaran alumni.
| Akses via: config('wilayah.list_3t'), config('wilayah.koordinat_kabupaten')
|
| Catatan: nama kabupaten/kota HARUS SAMA PERSIS dengan kolom
| `kabupaten_kota` di database (HURUF KAPITAL + awalan KABUPATEN/KOTA).
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Daftar Daerah 3T (Terdepan, Terluar, Tertinggal)
    |--------------------------------------------------------------------------
    */

    // Daerah TERTINGGAL — Perpres No. 63 Tahun 2020 (62 kabupaten, 2020–2024)
    'tertinggal' => [
        // Sumatera (7)
        'KABUPATEN NIAS',
        'KABUPATEN NIAS SELATAN',
        'KABUPATEN NIAS UTARA',
        'KABUPATEN NIAS BARAT',
        'KABUPATEN KEPULAUAN MENTAWAI',
        'KABUPATEN MUSI RAWAS UTARA',
        'KABUPATEN PESISIR BARAT',

        // Nusa Tenggara (14)
        'KABUPATEN LOMBOK UTARA',
        'KABUPATEN SUMBA BARAT',
        'KABUPATEN SUMBA TIMUR',
        'KABUPATEN KUPANG',
        'KABUPATEN TIMOR TENGAH SELATAN',
        'KABUPATEN BELU',
        'KABUPATEN ALOR',
        'KABUPATEN LEMBATA',
        'KABUPATEN ROTE NDAO',
        'KABUPATEN SUMBA TENGAH',
        'KABUPATEN SUMBA BARAT DAYA',
        'KABUPATEN MANGGARAI TIMUR',
        'KABUPATEN SABU RAIJUA',
        'KABUPATEN MALAKA',

        // Sulawesi (3)
        'KABUPATEN DONGGALA',
        'KABUPATEN TOJO UNA-UNA',
        'KABUPATEN SIGI',

        // Maluku (8)
        'KABUPATEN KEPULAUAN TANIMBAR', // dulu: Maluku Tenggara Barat
        'KABUPATEN KEPULAUAN ARU',
        'KABUPATEN SERAM BAGIAN BARAT',
        'KABUPATEN SERAM BAGIAN TIMUR',
        'KABUPATEN MALUKU BARAT DAYA',
        'KABUPATEN BURU SELATAN',
        'KABUPATEN KEPULAUAN SULA',
        'KABUPATEN PULAU TALIABU',

        // Papua (22)
        'KABUPATEN JAYAWIJAYA',
        'KABUPATEN NABIRE',
        'KABUPATEN PANIAI',
        'KABUPATEN PUNCAK JAYA',
        'KABUPATEN BOVEN DIGOEL',
        'KABUPATEN MAPPI',
        'KABUPATEN ASMAT',
        'KABUPATEN YAHUKIMO',
        'KABUPATEN PEGUNUNGAN BINTANG',
        'KABUPATEN TOLIKARA',
        'KABUPATEN KEEROM',
        'KABUPATEN WAROPEN',
        'KABUPATEN SUPIORI',
        'KABUPATEN MAMBERAMO RAYA',
        'KABUPATEN NDUGA',
        'KABUPATEN LANNY JAYA',
        'KABUPATEN MAMBERAMO TENGAH',
        'KABUPATEN YALIMO',
        'KABUPATEN PUNCAK',
        'KABUPATEN DOGIYAI',
        'KABUPATEN INTAN JAYA',
        'KABUPATEN DEIYAI',

        // Papua Barat (8)
        'KABUPATEN TELUK WONDAMA',
        'KABUPATEN TELUK BINTUNI',
        'KABUPATEN SORONG SELATAN',
        'KABUPATEN SORONG',
        'KABUPATEN TAMBRAUW',
        'KABUPATEN MAYBRAT',
        'KABUPATEN MANOKWARI SELATAN',
        'KABUPATEN PEGUNUNGAN ARFAK',
    ],

    // Daerah TERDEPAN & TERLUAR (kawasan perbatasan) — isi menyusul, verifikasi ke BNPP
    'perbatasan' => [
        // 'KABUPATEN NUNUKAN',
        // 'KABUPATEN KAPUAS HULU',
        // ...
    ],

    /*
    |--------------------------------------------------------------------------
    | Koordinat Kabupaten/Kota (untuk marker peta)
    |--------------------------------------------------------------------------
    | Format: 'NAMA' => [latitude, longitude]. Ditambah bertahap sesuai data.
    */

    'koordinat_kota_kabupaten' => [
        'KOTA CIREBON'      => [-6.7063, 108.5571],
        'KOTA BANDUNG'      => [-6.9175, 107.6191],
        'KABUPATEN BANDUNG' => [-7.0250, 107.5688],
    ],

    /*
    |--------------------------------------------------------------------------
    | Koordinat Provinsi (untuk dropdown & pindah peta)
    |--------------------------------------------------------------------------
    | Format: 'NAMA PROVINSI' => [latitude, longitude]
    */

    'koordinat_provinsi' => [
        'Aceh'                 => [4.695135, 96.749397],
        'Sumatera Utara'       => [2.115354, 99.545097],
        'Sumatera Barat'       => [-0.739940, 100.800005],
        'Riau'                 => [0.293347, 101.706829],
        'Kepulauan Riau'       => [3.945651, 108.142867],
        'Jambi'                => [-1.610123, 103.613121],
        'Sumatera Selatan'     => [-3.319437, 103.914398],
        'Bangka Belitung'      => [-2.741051, 106.440587],
        'Bengkulu'             => [-3.792845, 102.260765],
        'Lampung'              => [-4.558585, 105.406807],
        'DKI Jakarta'          => [-6.208763, 106.845599],
        'Jawa Barat'           => [-6.914744, 107.609810],
        'Banten'               => [-6.405817, 106.064018],
        'Jawa Tengah'          => [-7.150975, 110.140259],
        'DI Yogyakarta'        => [-7.797068, 110.370529],
        'Jawa Timur'           => [-7.536064, 112.238402],
        'Bali'                 => [-8.409518, 115.188919],
        'Nusa Tenggara Barat'  => [-8.652933, 117.361648],
        'Nusa Tenggara Timur'  => [-8.657382, 121.079370],
        'Kalimantan Barat'     => [-0.278781, 111.475285],
        'Kalimantan Tengah'    => [-1.681488, 113.382355],
        'Kalimantan Selatan'   => [-3.092642, 115.283758],
        'Kalimantan Timur'     => [0.538659, 116.419389],
        'Kalimantan Utara'     => [3.073030, 116.041887],
        'Sulawesi Utara'       => [0.624693, 123.975002],
        'Gorontalo'            => [0.699944, 122.446724],
        'Sulawesi Tengah'      => [-1.430025, 121.445618],
        'Sulawesi Barat'       => [-2.844137, 119.232078],
        'Sulawesi Selatan'     => [-3.668800, 119.974053],
        'Sulawesi Tenggara'    => [-4.144910, 122.174605],
        'Maluku'               => [-3.238462, 130.145273],
        'Maluku Utara'         => [1.570999, 127.808769],
        'Papua Barat'          => [-1.336115, 133.174716],
        'Papua'                => [-4.269928, 138.080353],
    ],
];