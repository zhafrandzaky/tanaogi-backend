<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDriverRequest;
use App\Http\Requests\Admin\UpdateDriverRequest;
use App\Http\Resources\V1\DriverResource;
use App\Http\Resources\V1\DriverScheduleResource;
use App\Services\DriverService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DriverService $driverService
    ) {}

    public function index(): JsonResponse
    {
        $drivers = $this->driverService->getAll();

        return $this->success(
            'Data driver berhasil diambil',
            DriverResource::collection($drivers)
        );
    }

    public function store(StoreDriverRequest $request): JsonResponse
    {
        $driver = $this->driverService->create($request->validated());

        return $this->success(
            'Driver berhasil dibuat',
            DriverResource::make($driver),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $driver = $this->driverService->findById($id);

        return $this->success(
            'Detail driver berhasil diambil',
            DriverResource::make($driver)
        );
    }

    public function update(UpdateDriverRequest $request, string $id): JsonResponse
    {
        $driver = $this->driverService->update($id, $request->validated());

        return $this->success(
            'Driver berhasil diperbarui',
            DriverResource::make($driver)
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->driverService->delete($id);

        return $this->success('Driver berhasil dihapus');
    }

    public function toggleActive(string $id): JsonResponse
    {
        $driver = $this->driverService->toggleActive($id);

        return $this->success(
            'Status berhasil diubah',
            ['id' => $driver->id, 'is_active' => $driver->is_active]
        );
    }

    public function schedule(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year'  => ['required', 'integer', 'min:2020', 'max:2100'],
        ], [
            'month.required' => 'Bulan wajib diisi',
            'month.min'      => 'Bulan harus antara 1-12',
            'month.max'      => 'Bulan harus antara 1-12',
            'year.required'  => 'Tahun wajib diisi',
            'year.min'       => 'Tahun tidak valid',
            'year.max'       => 'Tahun tidak valid',
        ]);

        $schedule = $this->driverService->getSchedule(
            $id,
            (int) $request->input('month'),
            (int) $request->input('year')
        );

        return $this->success(
            'Jadwal driver berhasil diambil',
            new DriverScheduleResource((object) $schedule)
        );
    }
}
