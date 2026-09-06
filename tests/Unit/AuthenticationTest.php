<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Tests\Unit;

use Emanate\BeemSms\BeemSms;
use Emanate\BeemSms\Exceptions\InvalidBeemApiKey;
use Emanate\BeemSms\Tests\TestCase;
use InvalidArgumentException;
use ReflectionMethod;

class AuthenticationTest extends TestCase
{
    public function testBasicAuthIsUsedWhenNoAccessTokenIsConfigured(): void
    {
        $options = $this->httpOptions(new BeemSms());

        $this->assertSame(['test-api-key', 'test-secret-key'], $options['auth']);
        $this->assertArrayNotHasKey('Authorization', $options['headers']);
    }

    public function testAccessTokenFromConfigReplacesBasicAuth(): void
    {
        config()->set('beem.access_token', 'token-from-config');

        $options = $this->httpOptions(new BeemSms());

        $this->assertArrayNotHasKey('auth', $options);
        $this->assertSame('token-from-config', $options['headers']['Authorization']);
    }

    public function testAccessTokenCanBeSetAtRuntime(): void
    {
        $options = $this->httpOptions((new BeemSms())->accessToken('runtime-token'));

        $this->assertArrayNotHasKey('auth', $options);
        $this->assertSame('runtime-token', $options['headers']['Authorization']);
    }

    public function testTheTokenIsSentWithoutABearerPrefix(): void
    {
        $options = $this->httpOptions((new BeemSms())->accessToken('runtime-token'));

        $this->assertStringStartsNotWith('Bearer', $options['headers']['Authorization']);
    }

    public function testWithoutAccessTokenFallsBackToBasicAuth(): void
    {
        config()->set('beem.access_token', 'token-from-config');

        $options = $this->httpOptions((new BeemSms())->withoutAccessToken());

        $this->assertSame(['test-api-key', 'test-secret-key'], $options['auth']);
        $this->assertArrayNotHasKey('Authorization', $options['headers']);
    }

    public function testAnEmptyAccessTokenIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BeemSms())->accessToken('');
    }

    public function testAConfiguredAccessTokenRemovesTheNeedForAnApiKey(): void
    {
        config()->set('beem.api_key', '');
        config()->set('beem.secret_key', '');
        config()->set('beem.access_token', 'token-from-config');

        $options = $this->httpOptions(new BeemSms());

        $this->assertSame('token-from-config', $options['headers']['Authorization']);
    }

    public function testTheApiKeyIsStillRequiredWithoutAnAccessToken(): void
    {
        config()->set('beem.api_key', '');

        $this->expectException(InvalidBeemApiKey::class);

        new BeemSms();
    }

    public function testCallerSuppliedHeadersAreKept(): void
    {
        $options = $this->httpOptions(
            (new BeemSms())->accessToken('runtime-token'),
            ['headers' => ['X-Custom' => 'value']]
        );

        $this->assertSame('value', $options['headers']['X-Custom']);
        $this->assertSame('runtime-token', $options['headers']['Authorization']);
        $this->assertSame('application/json', $options['headers']['Accept']);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('beem.api_key', 'test-api-key');
        $app['config']->set('beem.secret_key', 'test-secret-key');
        $app['config']->set('beem.sender_name', 'INFO');
        $app['config']->set('beem.access_token', null);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function httpOptions(BeemSms $beem, array $options = []): array
    {
        $method = new ReflectionMethod($beem, 'httpOptions');
        $method->setAccessible(true);

        return $method->invoke($beem, $options);
    }
}
