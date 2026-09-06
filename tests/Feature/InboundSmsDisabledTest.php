<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Tests\Feature;

use Emanate\BeemSms\Tests\TestCase;

class InboundSmsDisabledTest extends TestCase
{
    public function testTheRouteIsNotRegisteredWhenTwoWayIsDisabled(): void
    {
        $this->assertFalse($this->app['router']->has('beem.inbound'));
    }

    public function testTheCallbackPathIsNotRoutableWhenTwoWayIsDisabled(): void
    {
        $this->postJson('beem/inbound', [])->assertNotFound();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('beem.api_key', 'test-api-key');
        $app['config']->set('beem.secret_key', 'test-secret-key');
        $app['config']->set('beem.sender_name', 'INFO');
        $app['config']->set('beem.two_way.enabled', false);
    }
}
