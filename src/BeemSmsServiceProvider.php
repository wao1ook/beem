<?php

namespace Emanate\BeemSms;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Emanate\BeemSms\Commands\BeemSmsCommand;

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
