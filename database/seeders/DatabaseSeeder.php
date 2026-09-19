<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun admin default untuk masuk ke panel /admin.
        User::firstOrCreate(
            ['email' => 'admin@antrian.test'],
            [
                'name'     => 'Administrator',
                'role'     => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // Akun operator loket default (dua petugas untuk uji penguncian loket).
        User::firstOrCreate(
            ['email' => 'operator1@antrian.test'],
            [
                'name'     => 'Operator 1',
                'role'     => 'operator',
                'password' => Hash::make('password'),
            ]
        );
        User::firstOrCreate(
            ['email' => 'operator2@antrian.test'],
            [
                'name'     => 'Operator 2',
                'role'     => 'operator',
                'password' => Hash::make('password'),
            ]
        );

        // Data contoh layanan & loket.
        $this->call(QueueSeeder::class);
    }
}
