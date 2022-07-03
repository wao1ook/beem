<?php

namespace Emanate\BeemSms;

use Emanate\BeemSms\Commands\BeemSmsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BeemSmsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('beem-sms')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_beem-sms_table')
            ->hasCommand(BeemSmsCommand::class);
    }
}
