<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $destinations = Destination::all();
        
        $reviewPool = [
            [
                'rating' => 5,
                'comment' => 'Tempat yang sangat indah dan memukau! Pengalaman yang tidak akan pernah terlupakan di Sulawesi Selatan.'
            ],
            [
                'rating' => 5,
                'comment' => 'Pelayanan ramah, pemandangannya luar biasa menakjubkan. Sangat direkomendasikan untuk dikunjungi!'
            ],
            [
                'rating' => 4,
                'comment' => 'Sangat menikmati kunjungan ke sini. Fasilitasnya lengkap dan bersih, cocok untuk liburan keluarga.'
            ],
            [
                'rating' => 5,
                'comment' => 'Destinasi eksotis yang wajib dikunjungi minimal sekali seumur hidup. Suasananya tenang dan damai.'
            ],
            [
                'rating' => 4,
                'comment' => 'Sangat mengagumkan. Akses jalan sudah cukup baik dan masyarakat lokal sangat ramah menyambut wisatawan.'
            ]
        ];

        foreach ($destinations as $dest) {
            // Check if there are already reviews for this destination to avoid duplicates
            if (Review::where('destination_id', $dest->id)->count() >= 2) {
                continue;
            }

            // Insert 2 reviews
            for ($i = 0; $i < 2; $i++) {
                $user = $users[$i % $users->count()];
                $poolIndex = rand(0, count($reviewPool) - 1);
                
                Review::create([
                    'user_id' => $user->id,
                    'destination_id' => $dest->id,
                    'rating' => $reviewPool[$poolIndex]['rating'],
                    'comment' => $reviewPool[$poolIndex]['comment'],
                ]);
            }
        }
    }
}
