<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@tanaogi.zyy.my.id');
        $password = env('ADMIN_PASSWORD', 'password');
        $name     = env('ADMIN_NAME', 'Admin TanaOgi');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
            ]
        );

        $user->assignRole('admin');
    }
}
