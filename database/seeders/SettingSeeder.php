<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key'   => 'admin_whatsapp',
                'value' => '628xxx',
            ],
            [
                'key'   => 'wa_template_driver_order',
                'value' => "Halo {driver_name}, Anda mendapatkan pesanan baru:\n\nPelanggan: {user_name}\nNo HP: {user_phone}\nDestinasi: {destination}\nTanggal Berangkat: {departure_date}\nTanggal Kembali: {return_date}\nLokasi Jemput: {pickup_location}\nCatatan: {notes}\n\nSilakan konfirmasi pesanan ini.",
            ],
            [
                'key'   => 'reminder_hours_before_pickup',
                'value' => '3',
            ],
            [
                'key'   => 'max_requests_per_minute',
                'value' => '60',
            ],
            [
                'key'   => 'max_orders_per_phone',
                'value' => '3',
            ],
            [
                'key'   => 'orders_window_hours',
                'value' => '24',
            ],
            [
                'key'   => 'auto_ban_enabled',
                'value' => 'true',
            ],
            [
                'key'   => 'auto_ban_duration_minutes',
                'value' => '60',
            ],
            [
                'key'   => 'is_maintenance',
                'value' => 'false',
            ],
        ];

        $now = now();

        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'id'         => Str::uuid()->toString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
