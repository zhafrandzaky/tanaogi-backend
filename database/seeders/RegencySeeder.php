<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegencySeeder extends Seeder
{
    public function run(): void
    {
        $regencies = [
            'Makassar', 'Gowa', 'Takalar', 'Jeneponto', 'Bantaeng',
            'Bulukumba', 'Selayar', 'Sinjai', 'Bone', 'Soppeng',
            'Wajo', 'Sidrap', 'Pinrang', 'Enrekang', 'Luwu',
            'Luwu Utara', 'Luwu Timur', 'Palopo', 'Tana Toraja', 'Toraja Utara',
            'Maros', 'Pangkep', 'Barru', 'Pare-Pare',
        ];

        $now = now();

        foreach ($regencies as $name) {
            DB::table('regencies')->insert([
                'id'         => Str::uuid()->toString(),
                'name'       => $name,
                'slug'       => Str::slug($name),
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
