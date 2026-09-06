<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Emanate\BeemSms\Otp apiKey(string $apiKey)
 * @method static \Emanate\BeemSms\Otp secretKey(string $secretKey)
 * @method static \Emanate\BeemSms\Otp accessToken(string $accessToken)
 * @method static \Emanate\BeemSms\Otp appId(int $appId)
 * @method static \Emanate\BeemSms\Otp parameter(string $name, mixed $value)
 * @method static array request(string $phoneAddress)
 * @method static array verify(string $pinId, string $pin)
 * @method static bool check(string $pinId, string $pin)
 *
 * @see \Emanate\BeemSms\Otp
 */
final class Otp extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'beem-otp';
    }
}
