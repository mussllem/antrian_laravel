<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Batas Idle Loket (menit)
    |--------------------------------------------------------------------------
    |
    | Loket dianggap "basi" bila operatornya tidak mengirim heartbeat (mis.
    | menutup browser, kehilangan koneksi, atau logout paksa) melebihi jumlah
    | menit ini. Loket basi otomatis dibebaskan sehingga operator lain bisa
    | memakainya kembali.
    |
    */

    'loket_idle_timeout' => (int) env('LOKET_IDLE_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Interval Heartbeat (detik)
    |--------------------------------------------------------------------------
    |
    | Seberapa sering panel operator mengirim sinyal "masih aktif" ke server
    | untuk memperbarui occupied_at. Harus jauh lebih kecil dari timeout.
    |
    */

    'heartbeat_interval' => (int) env('LOKET_HEARTBEAT_INTERVAL', 60),

];
