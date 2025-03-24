<?php

namespace MarJose123\Pitaka;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PitakaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('pitaka')
            ->hasConfigFile();
        //            ->hasMigrations('create_pitaka_table')
    }
}
