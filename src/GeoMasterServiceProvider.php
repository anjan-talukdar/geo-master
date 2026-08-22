<?php

namespace AnjanTalukdar\GeoMaster;

use Illuminate\Support\ServiceProvider;
use AnjanTalukdar\GeoMaster\Console\Commands\InstallGeoMasterCommand;
use AnjanTalukdar\GeoMaster\Console\Commands\SeedGeoMasterCommand;
use AnjanTalukdar\GeoMaster\Console\Commands\SplitDistrictsCommand;

class GeoMasterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallGeoMasterCommand::class,
                SeedGeoMasterCommand::class,
                SplitDistrictsCommand::class,
            ]);
        }
    }
}
