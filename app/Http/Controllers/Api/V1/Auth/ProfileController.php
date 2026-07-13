<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    use ApiResponse;

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validatedData = $request->validated();

        if ($request->hasFile('avatar')) {
            $berkasFoto = $request->file('avatar');
            $extension = $berkasFoto->getClientOriginalExtension() ?: 'png';
            $jalurSimpan = 'avatars/' . Str::uuid() . '.' . $extension;

            Storage::disk('r2')->put($jalurSimpan, file_get_contents($berkasFoto), 'public');
            
            $validatedData['avatar'] = Storage::disk('r2')->url($jalurSimpan);
        }

        $user->update($validatedData);

        return $this->success('Profile updated successfully', [
            'user' => UserResource::make($user),
        ]);
    }
}
