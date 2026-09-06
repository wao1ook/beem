<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Tests\Unit;

use Emanate\BeemSms\Exceptions\InvalidBeemApiKey;
use Emanate\BeemSms\Exceptions\OtpException;
use Emanate\BeemSms\Otp;
use Emanate\BeemSms\Tests\TestCase;
use InvalidArgumentException;
use ReflectionProperty;
use RuntimeException;

class OtpTest extends TestCase
{
    public function testTheApplicationIdIsReadFromConfig(): void
    {
        $this->assertSame(1234, $this->appId(new Otp()));
    }

    public function testTheApplicationIdCanBeOverriddenAtRuntime(): void
    {
        $this->assertSame(99, $this->appId((new Otp())->appId(99)));
    }

    public function testRequestingWithoutAnApplicationIdIsRejected(): void
    {
        config()->set('beem.otp.app_id', null);

        $this->expectException(RuntimeException::class);

        (new Otp())->request('255700000000');
    }

    public function testRequestingWithAnEmptyPhoneAddressIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Otp())->request('+');
    }

    public function testVerifyingWithAnEmptyPinIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Otp())->verify('a-pin-id', '');
    }

    public function testTheApiKeyIsRequiredWithoutAnAccessToken(): void
    {
        config()->set('beem.api_key', '');

        $this->expectException(InvalidBeemApiKey::class);

        new Otp();
    }

    public function testAnAccessTokenRemovesTheNeedForAnApiKey(): void
    {
        config()->set('beem.api_key', '');
        config()->set('beem.secret_key', '');
        config()->set('beem.access_token', 'token-from-config');

        $this->assertSame(1234, $this->appId(new Otp()));
    }

    public function testTheExceptionReadsTheNestedResponseCode(): void
    {
        $exception = OtpException::fromResponse([
            'data' => ['message' => ['code' => 114, 'message' => 'Incorrect PIN']],
        ]);

        $this->assertSame(114, $exception->getCode());
        $this->assertSame('Incorrect PIN', $exception->getMessage());
        $this->assertSame('Incorrect PIN - PIN sent is not correct.', $exception->description());
    }

    public function testTheExceptionFallsBackToTheDocumentedMessage(): void
    {
        $exception = OtpException::fromResponse(['data' => ['message' => ['code' => 115]]]);

        $this->assertSame(115, $exception->getCode());
        $this->assertSame('PIN timeout - PIN has expired.', $exception->getMessage());
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('beem.api_key', 'test-api-key');
        $app['config']->set('beem.secret_key', 'test-secret-key');
        $app['config']->set('beem.sender_name', 'INFO');
        $app['config']->set('beem.access_token', null);
        $app['config']->set('beem.otp.app_id', 1234);
    }

    protected function appId(Otp $otp): ?int
    {
        $property = new ReflectionProperty($otp, 'appId');
        $property->setAccessible(true);

        return $property->getValue($otp);
    }
}
