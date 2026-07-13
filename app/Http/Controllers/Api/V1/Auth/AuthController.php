<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $dataValidasi = $request->validated();

        if ($request->hasFile('avatar')) {
            $berkasFoto = $request->file('avatar');
            $jalurSimpan = 'avatars/' . Str::uuid() . '.' . $berkasFoto->getClientOriginalExtension();

            Storage::disk('r2')->put($jalurSimpan, file_get_contents($berkasFoto), 'public');
            $dataValidasi['avatar'] = Storage::disk('r2')->url($jalurSimpan);
        }

        $result = $this->authService->register($dataValidasi);

        return $this->success('User registered successfully', [
            'token' => $result['token'],
            'user' => UserResource::make($result['user']),
        ], 201);
    }
    
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), null, 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), null, 403);
        }

        return $this->success('Login berhasil', [
            'token' => $result['token'],
            'user' => UserResource::make($result['user']),
        ]);
    }

    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleCallback(Request $request): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->access_token);

            $result = $this->authService->googleLogin([
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
            ]);

            return $this->success('Login Google berhasil', [
                'token' => $result['token'],
                'user' => UserResource::make($result['user']),
            ]);
            
        } catch (\Exception $e) {
            return $this->error('Gagal memproses token Google: ' . $e->getMessage(), null, 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success('Logout berhasil');
    }
}