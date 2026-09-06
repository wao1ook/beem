<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Tests\Unit;

use Emanate\BeemSms\Exceptions\InvalidMulticountryCredentials;
use Emanate\BeemSms\Exceptions\MulticountrySmsException;
use Emanate\BeemSms\MulticountrySms;
use Emanate\BeemSms\Tests\TestCase;
use InvalidArgumentException;
use RuntimeException;

class MulticountrySmsTest extends TestCase
{
    public function testPayloadContainsTheDocumentedRequiredParameters(): void
    {
        $payload = (new MulticountrySms())
            ->from('255650000000')
            ->to('255650000001')
            ->content('Hello World')
            ->payload();

        $this->assertSame('test-user', $payload['USERNAME']);
        $this->assertSame('test-password', $payload['PASSWORD']);
        $this->assertSame('255650000000', $payload['SOURCEADDR']);
        $this->assertSame('255650000001', $payload['DESTADDR']);
        $this->assertSame('Hello World', $payload['MESSAGE']);
    }

    public function testAnAlphanumericSenderSetsTheAlphanumericTypeOfNumber(): void
    {
        $payload = (new MulticountrySms())
            ->from('MyApp')
            ->to('255650000001')
            ->content('Hello')
            ->payload();

        $this->assertSame(MulticountrySms::TON_ALPHANUMERIC, $payload['SOURCEADDRTON']);
    }

    public function testANumericSenderDoesNotSetTheAlphanumericTypeOfNumber(): void
    {
        $payload = (new MulticountrySms())
            ->from('255650000000')
            ->to('255650000001')
            ->content('Hello')
            ->payload();

        $this->assertArrayNotHasKey('SOURCEADDRTON', $payload);
    }

    public function testAnAlphanumericSenderLongerThanElevenCharactersIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new MulticountrySms())->from('TWELVECHARSX');
    }

    public function testALeadingPlusIsStrippedFromTheDestination(): void
    {
        $payload = (new MulticountrySms())
            ->from('255650000000')
            ->to('+255650000001')
            ->content('Hello')
            ->payload();

        $this->assertSame('255650000001', $payload['DESTADDR']);
    }

    public function testDeliveryReportsCanBeRequestedWithACallbackUrl(): void
    {
        $payload = (new MulticountrySms())
            ->from('255650000000')
            ->to('255650000001')
            ->content('Hello')
            ->deliveryReport('https://example.com/dlr')
            ->payload();

        $this->assertSame(1, $payload['DLR']);
        $this->assertSame('https://example.com/dlr', $payload['DLRADDRESS']);
    }

    public function testBinaryMessagesSetTheBinaryCharcode(): void
    {
        $payload = (new MulticountrySms())
            ->from('255650000000')
            ->to('255650000001')
            ->content('414243')
            ->binary()
            ->payload();

        $this->assertSame(MulticountrySms::CHARCODE_BINARY, $payload['CHARCODE']);
    }

    public function testConcatenationAndUserDataHeaderParametersAreSet(): void
    {
        $payload = (new MulticountrySms())
            ->from('255650000000')
            ->to('255650000001')
            ->content('Hello')
            ->userDataHeader('0605040B8423F0')
            ->concatenated(42, 1, 3)
            ->ports(9200, 2948)
            ->validityPeriod(3600)
            ->payload();

        $this->assertSame(1, $payload['UDHI']);
        $this->assertSame('0605040B8423F0', $payload['UDH']);
        $this->assertSame(42, $payload['CONCATSMSREF']);
        $this->assertSame(1, $payload['CONCATSMSSEQ']);
        $this->assertSame(3, $payload['CONCATSMSMAX']);
        $this->assertSame(9200, $payload['SOURCEPORT']);
        $this->assertSame(2948, $payload['DESTPORT']);
        $this->assertSame(3600, $payload['VP']);
    }

    public function testAnOutOfRangePortIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new MulticountrySms())->ports(0, 70000);
    }

    public function testArbitraryParametersAreUppercased(): void
    {
        $payload = (new MulticountrySms())
            ->from('255650000000')
            ->to('255650000001')
            ->content('Hello')
            ->parameter('sourceaddrnpi', 1)
            ->payload();

        $this->assertSame(1, $payload['SOURCEADDRNPI']);
    }

    public function testTheSourceAddressCanComeFromConfig(): void
    {
        config()->set('beem.multicountry.source_address', 'MyApp');

        $payload = (new MulticountrySms())
            ->to('255650000001')
            ->content('Hello')
            ->payload();

        $this->assertSame('MyApp', $payload['SOURCEADDR']);
    }

    public function testAMissingDestinationIsRejected(): void
    {
        $this->expectException(RuntimeException::class);

        (new MulticountrySms())->from('255650000000')->content('Hello')->payload();
    }

    public function testMissingCredentialsAreRejected(): void
    {
        config()->set('beem.multicountry.password', '');

        $this->expectException(InvalidMulticountryCredentials::class);

        new MulticountrySms();
    }

    public function testTheExceptionMapsPlatformStatusCodes(): void
    {
        $exception = MulticountrySmsException::fromResponse([
            'results' => [['status' => '16', 'statustext' => 'No credits']],
        ]);

        $this->assertSame(16, $exception->getCode());
        $this->assertSame('No credits', $exception->getMessage());
        $this->assertSame('No message credits left', $exception->description());
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('beem.multicountry.username', 'test-user');
        $app['config']->set('beem.multicountry.password', 'test-password');
        $app['config']->set('beem.multicountry.source_address', null);
    }
}
