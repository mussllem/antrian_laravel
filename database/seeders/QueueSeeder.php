<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\Service;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    public function run(): void
    {
        $umum = Service::create([
            'name' => 'Layanan Umum', 'prefix' => 'A', 'sort_order' => 1,
            'description' => 'Pendaftaran & informasi umum',
        ]);
        $bayar = Service::create([
            'name' => 'Pembayaran', 'prefix' => 'B', 'sort_order' => 2,
            'description' => 'Kasir & pembayaran',
        ]);
        $cs = Service::create([
            'name' => 'Customer Service', 'prefix' => 'C', 'sort_order' => 3,
            'description' => 'Pengaduan & bantuan',
        ]);

        $l1 = Counter::create(['name' => 'Loket 1', 'number' => 1, 'sort_order' => 1]);
        $l2 = Counter::create(['name' => 'Loket 2', 'number' => 2, 'sort_order' => 2]);
        $l3 = Counter::create(['name' => 'Loket 3', 'number' => 3, 'sort_order' => 3]);

        // Loket dinamis: satu loket bisa melayani beberapa layanan.
        $l1->services()->sync([$umum->id]);
        $l2->services()->sync([$umum->id, $bayar->id]);
        $l3->services()->sync([$cs->id]);
    }
}
