<?php

namespace Emanate\BeemSms;

use Emanate\BeemSms\Commands\BeemSmsCommand;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BeemSmsServiceProvider extends ServiceProvider
{
//    public function configurePackage(Package $package): void
//    {
//        /*
//         * This class is a Package Service Provider
//         *
//         * More info: https://github.com/spatie/laravel-package-tools
//         */
//        $package
//            ->name('beem-sms')
//            ->hasConfigFile()
//            ->hasCommand(BeemSmsCommand::class);
//
//        $this->app->singleton(BeemSms::class, function () {
//            return new BeemSms(config('beem.sms'));
//        });
//
//        Notification::resolved(function (ChannelManager $service) {
//            $service->extend('beem', function ($app) {
//                return new BeemSmsChannel(
//                    $app->make(BeemSms::class),
//                    $app['config']['beem-sms.sender_name']
//                );
//            });
//        });
//    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/beem-sms.php', 'beem-sms');

        $this->app->bind('beem-sms', function () {
            return new BeemSms(config('beem-sms'));
        });

//        $this->app->singleton(BeemSms::class, function () {
//            return new BeemSms(config('beem-sms'));
//        });

//        Notification::resolved(function (ChannelManager $service) {
//            $service->extend('beem', function ($app) {
//                return new BeemSmsChannel(
//                    $app->make(BeemSms::class),
//                    $app['config']['beem-sms.sender_name']
//                );
//            });
//        });
    }


    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/beem-sms.php' => $this->app->configPath('beem-sms.php'),
            ], 'beem-sms');
        }
    }
}
