<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Accommodation;
use Illuminate\Database\Seeder;

class RealAccommodationsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pantai Tanjung Bira
        $tanjungBira = Destination::where('name', 'Pantai Tanjung Bira')
            ->orWhere('name', 'like', '%Tanjung Bira%')
            ->first();

        if ($tanjungBira) {
            $accommodations = [
                [
                    'name'            => 'Amatoa Resort',
                    'type'            => 'resort',
                    'price_per_night' => 2150000,
                    'address'         => 'Desa Bira, Kecamatan Bonto Bahari, Bulukumba',
                    'latitude'        => -5.6105,
                    'longitude'       => 120.4590,
                ],
                [
                    'name'            => 'Teppo Resort',
                    'type'            => 'hotel',
                    'price_per_night' => 850000,
                    'address'         => 'Kawasan Tanjung Bira, Bulukumba',
                    'latitude'        => -5.6095,
                    'longitude'       => 120.4578,
                ],
                [
                    'name'            => 'Bira Highland',
                    'type'            => 'resort',
                    'price_per_night' => 1200000,
                    'address'         => 'Bira, Bonto Bahari, Bulukumba',
                    'latitude'        => -5.6080,
                    'longitude'       => 120.4560,
                ],
                [
                    'name'            => 'Cosmos Bungalows',
                    'type'            => 'homestay',
                    'price_per_night' => 650000,
                    'address'         => 'Pantai Bara, Bulukumba',
                    'latitude'        => -5.6050,
                    'longitude'       => 120.4500,
                ],
                [
                    'name'            => 'Hakuna Matata',
                    'type'            => 'resort',
                    'price_per_night' => 950000,
                    'address'         => 'Tanjung Bira, Bulukumba',
                    'latitude'        => -5.6110,
                    'longitude'       => 120.4580,
                ],
            ];

            foreach ($accommodations as $acc) {
                Accommodation::firstOrCreate(
                    ['name' => $acc['name']],
                    array_merge($acc, [
                        'destination_id' => $tanjungBira->id,
                        'is_active'      => true,
                    ])
                );
            }
        }

        // 2. Ke'te Kesu
        $keteKesu = Destination::where('name', 'like', '%Ke\'te%')
            ->orWhere('name', 'like', '%Kete%')
            ->first();

        if ($keteKesu) {
            $accommodations = [
                [
                    'name'            => 'Toraja Heritage Resort',
                    'type'            => 'resort',
                    'price_per_night' => 1500000,
                    'address'         => 'Jalan Raya Rantepao, Toraja Utara',
                    'latitude'        => -2.9620,
                    'longitude'       => 119.9180,
                ],
                [
                    'name'            => 'Batu Tumonga Homestay',
                    'type'            => 'homestay',
                    'price_per_night' => 450000,
                    'address'         => 'Lereng Gunung Sesean, Toraja Utara',
                    'latitude'        => -2.8850,
                    'longitude'       => 119.8920,
                ],
                [
                    'name'            => 'Lolai High Resort',
                    'type'            => 'resort',
                    'price_per_night' => 1100000,
                    'address'         => 'Negeri di Atas Awan Lolai, Toraja Utara',
                    'latitude'        => -2.9340,
                    'longitude'       => 119.9050,
                ],
                [
                    'name'            => 'Toraja Heritage Hotel',
                    'type'            => 'hotel',
                    'price_per_night' => 1450000,
                    'address'         => 'Rantepao, Toraja Utara',
                    'latitude'        => -2.9580,
                    'longitude'       => 119.9200,
                ],
                [
                    'name'            => 'Tongkonan Homestay',
                    'type'            => 'homestay',
                    'price_per_night' => 300000,
                    'address'         => 'Kawasan Desa Adat Kete Kesu, Toraja',
                    'latitude'        => -2.9599,
                    'longitude'       => 119.9215,
                ],
            ];

            foreach ($accommodations as $acc) {
                Accommodation::firstOrCreate(
                    ['name' => $acc['name']],
                    array_merge($acc, [
                        'destination_id' => $keteKesu->id,
                        'is_active'      => true,
                    ])
                );
            }
        }

        // 3. Hutan Karst Maros (if exists) or Pantai Bira as fallback
        $maros = Destination::where('name', 'like', '%Karst%')
            ->orWhere('name', 'like', '%Maros%')
            ->first();

        // Fallback to Pantai Bira if Maros destination is not seeded yet
        $targetDest = $maros ?: Destination::where('name', 'Pantai Bira')->first();

        if ($targetDest) {
            $accommodations = [
                [
                    'name'            => 'Maros Karst Eco Resort',
                    'type'            => 'resort',
                    'price_per_night' => 650000,
                    'address'         => 'Kawasan Rammang-Rammang, Maros',
                    'latitude'        => -4.9820,
                    'longitude'       => 119.7010,
                ],
                [
                    'name'            => 'Pute River Lodge',
                    'type'            => 'homestay',
                    'price_per_night' => 450000,
                    'address'         => 'Dermaga Rammang-Rammang, Maros',
                    'latitude'        => -4.9840,
                    'longitude'       => 119.7025,
                ],
                [
                    'name'            => 'Rammang Eco Lodge',
                    'type'            => 'homestay',
                    'price_per_night' => 550000,
                    'address'         => 'Desa Wisata Rammang-Rammang, Maros',
                    'latitude'        => -4.9810,
                    'longitude'       => 119.6990,
                ],
                [
                    'name'            => 'Hotel Transit Maros',
                    'type'            => 'hotel',
                    'price_per_night' => 350000,
                    'address'         => 'Jalan Poros Makassar-Maros',
                    'latitude'        => -5.0120,
                    'longitude'       => 119.5520,
                ],
                [
                    'name'            => 'Karst Cottage',
                    'type'            => 'homestay',
                    'price_per_night' => 400000,
                    'address'         => 'Lembah Berua, Maros',
                    'latitude'        => -4.9780,
                    'longitude'       => 119.7050,
                ],
            ];

            foreach ($accommodations as $acc) {
                Accommodation::firstOrCreate(
                    ['name' => $acc['name']],
                    array_merge($acc, [
                        'destination_id' => $targetDest->id,
                        'is_active'      => true,
                    ])
                );
            }
        }
    }
}
