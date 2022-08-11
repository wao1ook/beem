<?php

namespace Emanate\BeemSms;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BeemSmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/beem.php', 'beem');

        $this->app->bind('beem-sms', function () {
            return new BeemSms(config('beem'));
        });
    }


    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/beem.php' => $this->app->configPath('beem.php'),
            ], 'beem');
        }
    }
}
