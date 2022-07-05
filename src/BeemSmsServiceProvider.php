<?php

namespace Emanate\BeemSms;

use Emanate\BeemSms\Commands\BeemSmsCommand;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Channels\VonageSmsChannel;
use Illuminate\Support\Facades\Notification;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vonage\Client;

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
            ->hasCommand(BeemSmsCommand::class);

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('beem', function ($app) {
                return new BeemSmsChannel(
                    $app->make(BeemSms::class),
                    $app['config']['beem-sms.sms_from']
                );
            });
        });
    }
}
