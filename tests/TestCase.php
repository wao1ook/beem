<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Tests;

use Emanate\BeemSms\BeemSmsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BeemSmsServiceProvider::class,
        ];
    }
}
