<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Emanate\BeemSms\BeemSms apiKey(string $apiKey)
 * @method static \Emanate\BeemSms\BeemSms secretKey(string $secretKey)
 * @method static \Emanate\BeemSms\BeemSms senderName(string $senderName)
 * @method static \Emanate\BeemSms\BeemSms content(string $message)
 * @method static \Emanate\BeemSms\BeemSms encoding(int $encoding)
 * @method static \Emanate\BeemSms\BeemSms unicode()
 * @method static \Emanate\BeemSms\BeemSms scheduleTime(\DateTimeInterface|string $scheduleTime)
 * @method static \Emanate\BeemSms\BeemSms scheduleAt(\DateTimeInterface|string $scheduleTime)
 * @method static \Emanate\BeemSms\BeemSms jobName(string $jobName)
 * @method static \Emanate\BeemSms\BeemSms campaignTitle(string $campaignTitle)
 * @method static \Emanate\BeemSms\BeemSms getRecipients(array $recipients)
 * @method static \Emanate\BeemSms\BeemSms loadRecipients(mixed $collection, string $column = 'phone_number')
 * @method static \Emanate\BeemSms\BeemSms unpackRecipients(...$recipients)
 * @method static \Psr\Http\Message\ResponseInterface send()
 * @method static array sendAndParse()
 * @method static array payload()
 * @method static mixed balance()
 * @method static array deliveryReport(string $destinationAddress, string $requestId)
 * @method static array senderNames(array $filters = [])
 * @method static array templates(array $filters = [])
 * @method static array createTemplate(string $title, string $message)
 * @method static array updateTemplate(string $templateId, string $title, string $message)
 * @method static array deleteTemplate(string $templateId)
 *
 * @see \Emanate\BeemSms\BeemSms
 */
final class BeemSms extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'beem-sms';
    }
}
