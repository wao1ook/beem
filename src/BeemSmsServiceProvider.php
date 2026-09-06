<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use Emanate\BeemSms\Contracts\Validator;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

final class BeemSmsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/beem.php', 'beem');

        $this->app->bind('beem-sms', fn () => new BeemSms());

        $this->app->bind('beem-multicountry-sms', fn () => new MulticountrySms());

        $this->app->bind('beem-otp', fn () => new Otp());

        $this->app->bind(Validator::class, function () {
            $validatorClass = config('beem.validator_class', DefaultValidator::class);

            return new $validatorClass();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/beem.php' => $this->app->configPath('beem.php'),
            ], 'beem');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            Validator::class,
            BeemSms::class,
            MulticountrySms::class,
            Otp::class,
            'beem-sms',
            'beem-multicountry-sms',
            'beem-otp',
        ];
    }
}
