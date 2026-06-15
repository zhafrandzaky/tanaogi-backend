<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->userService->getAll();

        return $this->success(
            'Data user berhasil diambil',
            UserResource::collection($users)
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return $this->success(
            'User berhasil dibuat',
            UserResource::make($user),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        return $this->success(
            'Detail user berhasil diambil',
            UserResource::make($user)
        );
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());

        return $this->success(
            'User berhasil diperbarui',
            UserResource::make($user)
        );
    }

    public function destroy(\Illuminate\Http\Request $request, string $id): JsonResponse
    {
        try {
            $this->userService->delete($id, $request->user()->id);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), null, 403);
        }

        return $this->success('User berhasil dihapus');
    }
}
