<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Emanate\BeemSms\MulticountrySms username(string $username)
 * @method static \Emanate\BeemSms\MulticountrySms password(string $password)
 * @method static \Emanate\BeemSms\MulticountrySms from(string $sourceAddress)
 * @method static \Emanate\BeemSms\MulticountrySms to(string $destinationAddress)
 * @method static \Emanate\BeemSms\MulticountrySms content(string $message)
 * @method static \Emanate\BeemSms\MulticountrySms deliveryReport(?string $callbackUrl = null)
 * @method static \Emanate\BeemSms\MulticountrySms binary()
 * @method static \Emanate\BeemSms\MulticountrySms validityPeriod(int $seconds)
 * @method static \Emanate\BeemSms\MulticountrySms ports(int $sourcePort, int $destinationPort)
 * @method static \Emanate\BeemSms\MulticountrySms userDataHeader(string $header)
 * @method static \Emanate\BeemSms\MulticountrySms concatenated(int $reference, int $sequence, int $total)
 * @method static \Emanate\BeemSms\MulticountrySms parameter(string $name, string|int|float|bool $value)
 * @method static array payload()
 * @method static array send()
 * @method static array balance()
 *
 * @see \Emanate\BeemSms\MulticountrySms
 */
final class MulticountrySms extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'beem-multicountry-sms';
    }
}
