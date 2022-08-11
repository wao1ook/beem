<?php

namespace Emanate\BeemSms\Tests;

use Emanate\BeemSms\BeemSmsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Emanate\\BeemSms\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            BeemSmsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
//        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_beem-sms_table.php.stub';
        $migration->up();
        */
    }
}
