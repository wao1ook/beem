<?php

namespace Emanate\BeemSms;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class BeemSmsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/beem.php', 'beem');

        $this->app->bind('beem-sms', function ($app) {
            return new BeemSms;
        });

        $this->app->alias('Emanate\BeemSms\BeemSms', 'beem-sms');
    }



    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/beem.php' => $this->app->configPath('beem.php'),
            ], 'beem');
        }
    }


    public function provides()
    {
        return [
            BeemSms::class,
            'beem-sms'
        ];
    }
}
