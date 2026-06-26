<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

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
            // Membaca access_token yang dikirim via POST dari frontend
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
            // Mencegah error 500 dan memberikan pesan yang jelas ke frontend
            return $this->error('Gagal memproses token Google: ' . $e->getMessage(), null, 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success('Logout berhasil');
    }
}