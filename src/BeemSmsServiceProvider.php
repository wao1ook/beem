<?php

namespace Emanate\BeemSms;

use Illuminate\Support\ServiceProvider;

class BeemSmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/beem.php', 'beem');

        $this->app->bind('beem-sms', function () {
            return new BeemSms;
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
