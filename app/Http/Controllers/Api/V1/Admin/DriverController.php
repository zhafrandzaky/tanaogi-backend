<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DriverScheduleRequest;
use App\Http\Requests\Admin\StoreDriverRequest;
use App\Http\Requests\Admin\UpdateDriverRequest;
use App\Http\Resources\V1\DriverResource;
use App\Http\Resources\V1\DriverScheduleResource;
use App\Services\DriverService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DriverController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DriverService $driverService
    ) {}

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');
        $status = $request->query('status');
        $vehicleType = $request->query('vehicle_type');

        $paginator = $this->driverService->paginate($perPage, $search, $status, $vehicleType);

        return $this->paginated(
            'Data driver berhasil diambil',
            $paginator,
            DriverResource::collection($paginator->items())
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

    public function schedule(DriverScheduleRequest $request, string $id): JsonResponse
    {
        $schedule = $this->driverService->getSchedule(
            $id,
            (int) $request->validated('month'),
            (int) $request->validated('year')
        );

        return $this->success(
            'Jadwal driver berhasil diambil',
            new DriverScheduleResource((object) $schedule)
        );
    }
}
