<?php

namespace Database\Seeders;

use App\Models\Regency;
use App\Models\Destination;
use App\Models\Accommodation;
use App\Models\Driver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AddFiveMoreSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Add 5 Regencies
        $regenciesData = [
            ['name' => 'Palu', 'is_active' => true],
            ['name' => 'Mamuju', 'is_active' => true],
            ['name' => 'Kendari', 'is_active' => true],
            ['name' => 'Manado', 'is_active' => true],
            ['name' => 'Gorontalo', 'is_active' => true],
        ];

        $regencies = [];
        foreach ($regenciesData as $r) {
            $regencies[] = Regency::firstOrCreate(
                ['name' => $r['name']],
                [
                    'slug' => Str::slug($r['name']),
                    'is_active' => $r['is_active'],
                ]
            );
        }

        // 2. Add 5 Destinations
        $destinationsData = [
            [
                'name' => 'Taman Nasional Lore Lindu',
                'regency_id' => $regencies[0]->id, // Palu
                'description' => 'Taman nasional dengan keanekaragaman hayati tinggi dan megalit purba misterius.',
                'ticket_price' => 30000,
                'facilities' => ['Pemandu', 'Jalur Trekking', 'Camping Ground'],
                'route_text' => 'Dari Palu berkendara ke selatan sekitar 2-3 jam.',
                'latitude' => -1.4116,
                'longitude' => 120.1417,
            ],
            [
                'name' => 'Pulau Karampuang',
                'regency_id' => $regencies[1]->id, // Mamuju
                'description' => 'Pulau indah dengan terumbu karang menakjubkan tepat di depan kota Mamuju.',
                'ticket_price' => 15000,
                'facilities' => ['Dermaga Wisata', 'Penyewaan Alat Selam', 'Warung Lokal'],
                'route_text' => 'Naik perahu motor selama 15-20 menit dari dermaga kota Mamuju.',
                'latitude' => -2.6278,
                'longitude' => 118.8682,
            ],
            [
                'name' => 'Pantai Nirwana Kendari',
                'regency_id' => $regencies[2]->id, // Kendari
                'description' => 'Pantai pasir putih dengan gradasi air laut tiga warna yang sangat memukau mata.',
                'ticket_price' => 10000,
                'facilities' => ['Gazebo', 'Toilet', 'Sewa Ban'],
                'route_text' => 'Berkendara sekitar 20 menit dari pusat kota Kendari.',
                'latitude' => -4.0125,
                'longitude' => 122.6041,
            ],
            [
                'name' => 'Taman Nasional Bunaken',
                'regency_id' => $regencies[3]->id, // Manado
                'description' => 'Salah satu situs penyelaman bawah laut terbaik di dunia dengan keindahan terumbu karang spektakuler.',
                'ticket_price' => 50000,
                'facilities' => ['Kapal Kaca (Katamaran)', 'Alat Snorkeling', 'Instruktur Diving'],
                'route_text' => 'Menggunakan speedboat dari pelabuhan Manado sekitar 30-40 menit.',
                'latitude' => 1.6214,
                'longitude' => 124.7611,
            ],
            [
                'name' => 'Benteng Otanaha',
                'regency_id' => $regencies[4]->id, // Gorontalo
                'description' => 'Benteng bersejarah peninggalan abad ke-16 dengan panorama indah menghadap Danau Limboto.',
                'ticket_price' => 12000,
                'facilities' => ['Area Parkir Luas', 'Toko Cinderamata', 'Anak Tangga Akses'],
                'route_text' => 'Hanya berjarak sekitar 8 km dari pusat kota Gorontalo.',
                'latitude' => 0.5541,
                'longitude' => 122.9912,
            ],
        ];

        $destinations = [];
        foreach ($destinationsData as $d) {
            $destinations[] = Destination::firstOrCreate(
                ['name' => $d['name']],
                [
                    'regency_id' => $d['regency_id'],
                    'slug' => Str::slug($d['name']),
                    'description' => $d['description'],
                    'ticket_price' => $d['ticket_price'],
                    'facilities' => $d['facilities'],
                    'route_text' => $d['route_text'],
                    'latitude' => $d['latitude'],
                    'longitude' => $d['longitude'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Add 5 Accommodations
        $accommodationsData = [
            [
                'name' => 'Lore Lindu Jungle Lodge',
                'destination_id' => $destinations[0]->id,
                'type' => 'homestay',
                'price_per_night' => 250000,
                'address' => 'Desa Wuasa, Lore Utara, Poso',
                'latitude' => -1.4120,
                'longitude' => 120.1420,
            ],
            [
                'name' => 'Karampuang Dive Resort',
                'destination_id' => $destinations[1]->id,
                'type' => 'resort',
                'price_per_night' => 650000,
                'address' => 'Pulau Karampuang, Mamuju',
                'latitude' => -2.6280,
                'longitude' => 118.8685,
            ],
            [
                'name' => 'Nirwana Beach Hotel',
                'destination_id' => $destinations[2]->id,
                'type' => 'hotel',
                'price_per_night' => 450000,
                'address' => 'Jalan Pantai Nirwana, Kendari',
                'latitude' => -4.0130,
                'longitude' => 122.6045,
            ],
            [
                'name' => 'Bunaken Marine Resort',
                'destination_id' => $destinations[3]->id,
                'type' => 'resort',
                'price_per_night' => 850000,
                'address' => 'Pantai Bunaken, Manado',
                'latitude' => 1.6220,
                'longitude' => 124.7615,
            ],
            [
                'name' => 'Otanaha Heritage Homestay',
                'destination_id' => $destinations[4]->id,
                'type' => 'homestay',
                'price_per_night' => 200000,
                'address' => 'Kelurahan Dembe I, Gorontalo',
                'latitude' => 0.5545,
                'longitude' => 122.9915,
            ],
        ];

        foreach ($accommodationsData as $a) {
            Accommodation::firstOrCreate(
                ['name' => $a['name']],
                [
                    'destination_id' => $a['destination_id'],
                    'type' => $a['type'],
                    'price_per_night' => $a['price_per_night'],
                    'address' => $a['address'],
                    'latitude' => $a['latitude'],
                    'longitude' => $a['longitude'],
                    'is_active' => true,
                ]
            );
        }

        // 4. Add 5 Drivers
        $driversData = [
            ['name' => 'Budi Santoso', 'phone' => '081234567890', 'vehicle_type' => 'car'],
            ['name' => 'Agus Wijaya', 'phone' => '081398765432', 'vehicle_type' => 'car'],
            ['name' => 'Rahmat Hidayat', 'phone' => '081928374650', 'vehicle_type' => 'bus'],
            ['name' => 'Eko Prasetyo', 'phone' => '085712345678', 'vehicle_type' => 'car'],
            ['name' => 'Hendra Wijaya', 'phone' => '089876543210', 'vehicle_type' => 'bus'],
        ];

        foreach ($driversData as $dr) {
            Driver::firstOrCreate(
                ['phone' => $dr['phone']],
                [
                    'name' => $dr['name'],
                    'vehicle_type' => $dr['vehicle_type'],
                    'is_active' => true,
                ]
            );
        }
    }
}
