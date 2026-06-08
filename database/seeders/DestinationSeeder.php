<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Regency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $bulukumba   = Regency::where('name', 'Bulukumba')->first();
        $tanaToreja  = Regency::where('name', 'Tana Toraja')->first();
        $torajaUtara = Regency::where('name', 'Toraja Utara')->first();
        $wajo        = Regency::where('name', 'Wajo')->first();
        $makassar    = Regency::where('name', 'Makassar')->first();

        $destinations = [
            [
                'name'         => 'Pantai Tanjung Bira',
                'regency_id'   => $bulukumba?->id,
                'description'  => 'Pantai pasir putih yang sangat halus seperti tepung dengan air biru jernih di ujung selatan Sulawesi Selatan. Dikenal sebagai salah satu pantai terindah di Indonesia.',
                'ticket_price' => 25000,
                'facilities'   => ['Toilet', 'Parkir', 'Warung Makan', 'Area Snorkeling'],
                'route_text'   => 'Dari Makassar ke arah selatan via Trans Sulawesi menuju Bulukumba, lanjut ke Kecamatan Bonto Bahari. Jarak sekitar 200 km, waktu tempuh 3,5–4 jam.',
                'latitude'     => -5.6103,
                'longitude'    => 120.4586,
            ],
            [
                'name'         => 'Pantai Bira',
                'regency_id'   => $bulukumba?->id,
                'description'  => 'Pantai yang terletak di Desa Bira, Kecamatan Bonto Bahari, Bulukumba. Memiliki pasir putih dan pemandangan laut yang memukau dengan ombak yang tenang.',
                'ticket_price' => 20000,
                'facilities'   => ['Toilet', 'Parkir', 'Warung Makan'],
                'route_text'   => 'Dari Makassar menuju Bulukumba via jalur Trans Sulawesi, kemudian arahkan ke Desa Bira. Jarak sekitar 195 km, waktu tempuh 3–3,5 jam.',
                'latitude'     => -5.6089,
                'longitude'    => 120.4572,
            ],
            [
                'name'         => 'Lemo',
                'regency_id'   => $tanaToreja?->id,
                'description'  => 'Situs pemakaman tebing batu khas Toraja yang terukir secara tradisional. Tau-tau (patung kayu) berjajar di ceruk batu menjadi pemandangan yang sangat unik dan bersejarah.',
                'ticket_price' => 15000,
                'facilities'   => ['Toilet', 'Parkir', 'Pemandu Wisata'],
                'route_text'   => 'Dari Makassar menuju Makale via jalur darat Trans Sulawesi, sekitar 8–9 jam. Lemo terletak sekitar 12 km selatan Makale.',
                'latitude'     => -3.0617,
                'longitude'    => 119.8543,
            ],
            [
                'name'         => "Ke'te Kesu",
                'regency_id'   => $torajaUtara?->id,
                'description'  => "Kampung adat Toraja yang menjadi warisan budaya nasional. Terdapat rumah tongkonan tua, lumbung padi, dan kuburan tebing. Salah satu destinasi paling ikonik di Toraja Utara.",
                'ticket_price' => 20000,
                'facilities'   => ['Toilet', 'Parkir', 'Toko Souvenir', 'Pemandu Wisata'],
                'route_text'   => 'Dari Rantepao ke arah selatan sekitar 4 km. Dari Makassar, tempuh jalur darat sekitar 8–9 jam hingga Rantepao.',
                'latitude'     => -2.9597,
                'longitude'    => 119.9211,
            ],
            [
                'name'         => 'Danau Tempe',
                'regency_id'   => $wajo?->id,
                'description'  => 'Danau terbesar di Sulawesi Selatan yang menjadi rumah bagi komunitas nelayan di atas air. Wisatawan dapat menikmati keindahan danau, mengamati burung migran, dan mengenal budaya masyarakat Wajo.',
                'ticket_price' => 10000,
                'facilities'   => ['Toilet', 'Parkir', 'Perahu Wisata'],
                'route_text'   => 'Dari Makassar ke arah utara via Sengkang, Kabupaten Wajo. Jarak sekitar 250 km, waktu tempuh 4–5 jam.',
                'latitude'     => -3.9833,
                'longitude'    => 120.0167,
            ],
            [
                'name'         => 'Pantai Losari',
                'regency_id'   => $makassar?->id,
                'description'  => 'Ikon kota Makassar yang terkenal sebagai tempat menikmati sunset. Sepanjang jalan pesisir berjejer aneka kuliner khas Makassar dan menjadi ruang publik favorit warga kota.',
                'ticket_price' => 0,
                'facilities'   => ['Toilet', 'Parkir', 'Warung Makan', 'Area Foto'],
                'route_text'   => 'Berada di pusat Kota Makassar, Jalan Penghibur. Mudah dijangkau dari mana saja dalam kota.',
                'latitude'     => -5.1477,
                'longitude'    => 119.4063,
            ],
        ];

        foreach ($destinations as $data) {
            Destination::create([
                'regency_id'   => $data['regency_id'],
                'name'         => $data['name'],
                'slug'         => Str::slug($data['name']),
                'description'  => $data['description'],
                'ticket_price' => $data['ticket_price'],
                'facilities'   => $data['facilities'],
                'route_text'   => $data['route_text'],
                'latitude'     => $data['latitude'],
                'longitude'    => $data['longitude'],
                'is_active'    => true,
            ]);
        }
    }
}
