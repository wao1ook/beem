<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Tests\Unit;

use DateTimeImmutable;
use DateTimeZone;
use Emanate\BeemSms\BeemSms;
use Emanate\BeemSms\Exceptions\BeemApiException;
use Emanate\BeemSms\Tests\TestCase;
use InvalidArgumentException;
use RuntimeException;

class BeemSmsTest extends TestCase
{
    public function testPayloadContainsTheDocumentedRequiredFields(): void
    {
        $payload = (new BeemSms())
            ->content('Hello there')
            ->getRecipients(['255714000000'])
            ->payload();

        $this->assertSame('INFO', $payload['source_addr']);
        $this->assertSame('Hello there', $payload['message']);
        $this->assertSame(BeemSms::ENCODING_GSM, $payload['encoding']);
        $this->assertSame(
            [['recipient_id' => 1, 'dest_addr' => '255714000000']],
            $payload['recipients']
        );
    }

    public function testOptionalFieldsAreOmittedWhenNotSet(): void
    {
        $payload = (new BeemSms())
            ->content('Hello there')
            ->getRecipients(['255714000000'])
            ->payload();

        $this->assertArrayNotHasKey('schedule_time', $payload);
        $this->assertArrayNotHasKey('job_name', $payload);
        $this->assertArrayNotHasKey('campaign_title', $payload);
    }

    public function testRecipientIdsAreUniqueWithinTheRequest(): void
    {
        $payload = (new BeemSms())
            ->content('Hello there')
            ->getRecipients(['255714000000', '255734000000', '255744000000'])
            ->payload();

        $recipientIds = array_column($payload['recipients'], 'recipient_id');

        $this->assertSame($recipientIds, array_unique($recipientIds));
        $this->assertSame([1, 2, 3], $recipientIds);
    }

    public function testSenderNameCanBeOverridden(): void
    {
        $payload = (new BeemSms())
            ->content('Hello there')
            ->senderName('SHOP')
            ->getRecipients(['255714000000'])
            ->payload();

        $this->assertSame('SHOP', $payload['source_addr']);
    }

    public function testSenderNameRejectsValuesLongerThanElevenCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BeemSms())->senderName('TWELVECHARSX');
    }

    public function testEncodingCanBeSwitchedToUnicode(): void
    {
        $payload = (new BeemSms())
            ->content('Ujumbe wako')
            ->unicode()
            ->getRecipients(['255714000000'])
            ->payload();

        $this->assertSame(BeemSms::ENCODING_UNICODE, $payload['encoding']);
    }

    public function testEncodingDefaultIsReadFromConfig(): void
    {
        config()->set('beem.encoding', BeemSms::ENCODING_UNICODE);

        $payload = (new BeemSms())
            ->content('Ujumbe wako')
            ->getRecipients(['255714000000'])
            ->payload();

        $this->assertSame(BeemSms::ENCODING_UNICODE, $payload['encoding']);
    }

    public function testScheduleTimeAcceptsAString(): void
    {
        $payload = (new BeemSms())
            ->content('Hello there')
            ->getRecipients(['255714000000'])
            ->scheduleTime('2030-01-01 09:00')
            ->payload();

        $this->assertSame('2030-01-01 09:00', $payload['schedule_time']);
    }

    public function testScheduleTimeConvertsADateTimeToUtc(): void
    {
        $payload = (new BeemSms())
            ->content('Hello there')
            ->getRecipients(['255714000000'])
            ->scheduleTime(new DateTimeImmutable('2030-01-01 12:00', new DateTimeZone('Africa/Dar_es_Salaam')))
            ->payload();

        $this->assertSame('2030-01-01 09:00', $payload['schedule_time']);
    }

    public function testJobNameAndCampaignTitleAreIncluded(): void
    {
        $payload = (new BeemSms())
            ->content('Hello there')
            ->getRecipients(['255714000000'])
            ->jobName('welcome')
            ->campaignTitle('January onboarding')
            ->payload();

        $this->assertSame('welcome', $payload['job_name']);
        $this->assertSame('January onboarding', $payload['campaign_title']);
    }

    public function testRecipientsAboveTheDocumentedLimitAreRejected(): void
    {
        config()->set('beem.validate_phone_addresses', false);

        $this->expectException(RuntimeException::class);

        (new BeemSms())->getRecipients(array_fill(0, BeemSms::MAX_RECIPIENTS + 1, '255714000000'));
    }

    public function testApiExceptionMapsBeemResponseCodes(): void
    {
        $exception = BeemApiException::fromResponse(['code' => 102, 'message' => 'Insufficient balance']);

        $this->assertSame(102, $exception->getCode());
        $this->assertSame('Insufficient balance', $exception->getMessage());
        $this->assertSame('Insufficient balance to send SMS.', $exception->description());
        $this->assertSame(['code' => 102, 'message' => 'Insufficient balance'], $exception->response());
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('beem.api_key', 'test-api-key');
        $app['config']->set('beem.secret_key', 'test-secret-key');
        $app['config']->set('beem.sender_name', 'INFO');
    }
}
