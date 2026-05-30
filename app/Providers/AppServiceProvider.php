<?php

namespace App\Providers;

use App\Repositories\AccommodationRepository;
use App\Repositories\BlacklistIpRepository;
use App\Repositories\BlacklistPhoneRepository;
use App\Repositories\Contracts\AccommodationRepositoryInterface;
use App\Repositories\Contracts\BlacklistIpRepositoryInterface;
use App\Repositories\Contracts\BlacklistPhoneRepositoryInterface;
use App\Repositories\Contracts\DestinationRepositoryInterface;
use App\Repositories\Contracts\DriverOrderRepositoryInterface;
use App\Repositories\Contracts\DriverRepositoryInterface;
use App\Repositories\Contracts\RegencyRepositoryInterface;
use App\Repositories\Contracts\RequestLogRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use App\Repositories\Contracts\WhitelistIpRepositoryInterface;
use App\Repositories\Contracts\WhitelistPhoneRepositoryInterface;
use App\Repositories\DestinationRepository;
use App\Repositories\DriverOrderRepository;
use App\Repositories\DriverRepository;
use App\Repositories\RegencyRepository;
use App\Repositories\RequestLogRepository;
use App\Repositories\SettingRepository;
use App\Repositories\UserRepository;
use App\Repositories\VehicleRepository;
use App\Repositories\WhitelistIpRepository;
use App\Repositories\WhitelistPhoneRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RegencyRepositoryInterface::class, RegencyRepository::class);
        $this->app->bind(DestinationRepositoryInterface::class, DestinationRepository::class);
        $this->app->bind(DriverRepositoryInterface::class, DriverRepository::class);
        $this->app->bind(DriverOrderRepositoryInterface::class, DriverOrderRepository::class);
        $this->app->bind(AccommodationRepositoryInterface::class, AccommodationRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(BlacklistIpRepositoryInterface::class, BlacklistIpRepository::class);
        $this->app->bind(BlacklistPhoneRepositoryInterface::class, BlacklistPhoneRepository::class);
        $this->app->bind(WhitelistIpRepositoryInterface::class, WhitelistIpRepository::class);
        $this->app->bind(WhitelistPhoneRepositoryInterface::class, WhitelistPhoneRepository::class);
        $this->app->bind(RequestLogRepositoryInterface::class, RequestLogRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
