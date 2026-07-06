<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data): array
    {
        // Create new user in database
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'whatsapp' => $data['whatsapp'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        // Generate token for auto-login after register
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
    
    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Email atau password salah');
        }

        if (! $user->hasRole('admin')) {
            throw new AuthorizationException('Anda tidak memiliki akses');
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    public function googleLogin(array $googleUser): array
    {
        // Cari user berdasarkan email Google, atau buat baru jika belum ada
        $user = \App\Models\User::firstOrCreate(
            ['email' => $googleUser['email']],
            [
                'name' => $googleUser['name'],
                'password' => Hash::make(str()->random(24)), // Random password untuk user social
            ]
        );

        // Buat token
        $token = $user->createToken('google-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}