<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Tests\Feature;

use Emanate\BeemSms\Events\InboundSmsReceived;
use Emanate\BeemSms\InboundMessage;
use Emanate\BeemSms\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class InboundSmsTest extends TestCase
{
    /**
     * The payload shape documented for the Two-Way SMS callback.
     *
     * @var array<string, mixed>
     */
    protected array $payload = [
        'from' => '255701000000',
        'to' => '255701000001',
        'channel' => 'sms',
        'timeUTC' => 'Wed, 14 Jun 2017 07:00:00 GMT',
        'transaction_id' => '120a1039103910',
        'message' => [
            'text' => 'STOP please',
            'media' => ['mediaUrl' => ''],
            'custom' => [],
        ],
        'billing' => [
            'currency' => 'TZS',
            'subscriber_price' => '100.00',
            'billing_price' => '100.00',
        ],
    ];

    public function testAnAuthenticatedCallbackIsAcknowledged(): void
    {
        $this->postJson('beem/inbound', $this->payload, $this->basicAuthHeader())
            ->assertOk()
            ->assertExactJson(['transaction_id' => '120a1039103910', 'successful' => true]);
    }

    public function testAnAuthenticatedCallbackDispatchesTheEvent(): void
    {
        Event::fake();

        $this->postJson('beem/inbound', $this->payload, $this->basicAuthHeader())->assertOk();

        Event::assertDispatched(
            InboundSmsReceived::class,
            fn (InboundSmsReceived $event) => $event->message->from() === '255701000000'
                && $event->message->text() === 'STOP please'
        );
    }

    public function testAnUnauthenticatedCallbackIsRejected(): void
    {
        Event::fake();

        $this->postJson('beem/inbound', $this->payload)->assertStatus(401);

        Event::assertNotDispatched(InboundSmsReceived::class);
    }

    public function testTheWrongCredentialsAreRejected(): void
    {
        $header = ['Authorization' => 'Basic ' . base64_encode('wrong:credentials')];

        $this->postJson('beem/inbound', $this->payload, $header)->assertStatus(401);
    }

    public function testADedicatedCallbackTokenIsAccepted(): void
    {
        config()->set('beem.two_way.token', 'callback-token');

        $this->postJson('beem/inbound', $this->payload, ['Authorization' => 'callback-token'])
            ->assertOk();
    }

    public function testABearerPrefixIsTolerated(): void
    {
        config()->set('beem.two_way.token', 'callback-token');

        $this->postJson('beem/inbound', $this->payload, ['Authorization' => 'Bearer callback-token'])
            ->assertOk();
    }

    public function testVerificationCanBeTurnedOff(): void
    {
        config()->set('beem.two_way.verify_credentials', false);

        $this->postJson('beem/inbound', $this->payload)->assertOk();
    }

    public function testTheRouteIsRegisteredWhenTwoWayIsEnabled(): void
    {
        $this->assertTrue($this->app['router']->has('beem.inbound'));
    }

    public function testTheMessageExposesTheDocumentedFields(): void
    {
        $message = InboundMessage::fromPayload($this->payload);

        $this->assertSame('255701000000', $message->from());
        $this->assertSame('255701000001', $message->to());
        $this->assertSame('sms', $message->channel());
        $this->assertSame('120a1039103910', $message->transactionId());
        $this->assertSame('Wed, 14 Jun 2017 07:00:00 GMT', $message->timeUtc());
        $this->assertSame('STOP please', $message->text());
        $this->assertSame('STOP', $message->keyword());
        $this->assertNull($message->mediaUrl());
        $this->assertSame([], $message->custom());
        $this->assertSame('TZS', $message->currency());
        $this->assertSame('100.00', $message->subscriberPrice());
        $this->assertSame('100.00', $message->billingPrice());
        $this->assertSame($this->payload, $message->toArray());
    }

    public function testAMissingMessageBodyDoesNotBreakTheAccessors(): void
    {
        $message = InboundMessage::fromPayload(['from' => '255701000000']);

        $this->assertNull($message->text());
        $this->assertNull($message->keyword());
        $this->assertSame([], $message->billing());
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('beem.api_key', 'test-api-key');
        $app['config']->set('beem.secret_key', 'test-secret-key');
        $app['config']->set('beem.sender_name', 'INFO');
        $app['config']->set('beem.access_token', null);
        $app['config']->set('beem.two_way.enabled', true);
        $app['config']->set('beem.two_way.path', 'beem/inbound');
        $app['config']->set('beem.two_way.middleware', []);
        $app['config']->set('beem.two_way.verify_credentials', true);
        $app['config']->set('beem.two_way.token', null);
    }

    /**
     * @return array<string, string>
     */
    protected function basicAuthHeader(): array
    {
        return ['Authorization' => 'Basic ' . base64_encode('test-api-key:test-secret-key')];
    }
}
