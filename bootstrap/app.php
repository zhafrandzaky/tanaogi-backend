<?php

use App\Exceptions\DriverNotAvailableException;
use App\Exceptions\PhoneBlacklistedException;
use App\Http\Middleware\CheckBlacklistIp;
use App\Http\Middleware\CheckMaintenance;
use App\Http\Middleware\RateLimitAndLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api_v1.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(CheckMaintenance::class);

        $middleware->appendToGroup('api', [
            CheckBlacklistIp::class,
            RateLimitAndLog::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Only handle API requests
            if (! $request->is('api/*')) {
                return null;
            }

            $errorResponse = function (string $message, mixed $errors, int $status) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors'  => $errors,
                ], $status);
            };

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return $errorResponse('Data tidak ditemukan', null, 404);
            }

            if ($e instanceof AuthenticationException) {
                return $errorResponse('Silakan login terlebih dahulu', null, 401);
            }

            if ($e instanceof AuthorizationException) {
                return $errorResponse('Anda tidak memiliki akses', null, 403);
            }

            if ($e instanceof ValidationException) {
                return $errorResponse('Data tidak valid', $e->errors(), 422);
            }

            if ($e instanceof DriverNotAvailableException) {
                return $errorResponse('Driver tidak tersedia di tanggal tersebut', null, 422);
            }

            if ($e instanceof PhoneBlacklistedException) {
                return $errorResponse($e->getMessage() ?: 'Nomor tidak dapat melakukan pemesanan', null, 403);
            }

            if ($e instanceof ThrottleRequestsException) {
                return $errorResponse('Terlalu banyak request. Coba lagi nanti', null, 429);
            }

            // Generic exception
            if (app()->environment('production')) {
                return $errorResponse('Terjadi kesalahan pada server', null, 500);
            }

            // Development: show actual message
            return $errorResponse($e->getMessage(), null, 500);
        });
    })->create();
