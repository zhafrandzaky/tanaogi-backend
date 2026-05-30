<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'type'          => 'car',
                'name'          => 'Mobil (Avanza/Innova)',
                'price_per_day' => 500000,
                'description'   => null,
                'is_active'     => true,
            ],
            [
                'type'          => 'bus',
                'name'          => 'Bus Pariwisata',
                'price_per_day' => 1500000,
                'description'   => null,
                'is_active'     => true,
            ],
        ];

        $now = now();

        foreach ($vehicles as $vehicle) {
            DB::table('vehicles')->insert(array_merge($vehicle, [
                'id'         => Str::uuid()->toString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
