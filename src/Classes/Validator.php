<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Classes;

use Emanate\BeemSms\Exceptions\InvalidPhoneAddress;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Validator
{
    /**
     * List of phone address prefixes.
     *
     * @var array
     */
    protected static array $phoneAddressPrefix = [
        '25562',
        '25565',
        '25567',
        '25568',
        '25569',
        '25571',
        '25574',
        '25575',
        '25576',
        '25578',
    ];

    /**
     * @return array
     */
    protected static function getPhoneAddressPrefix(): array
    {
        return static::$phoneAddressPrefix;
    }

    /**
     * @param  array  $phoneAddresses
     * @return bool
     */
    public function validate(array $phoneAddresses): bool
    {
        return $this->validatePhoneAddressPrefix($phoneAddresses)
            && $this->validatePhoneAddressLength($phoneAddresses);
    }

    /**
     * @param  array  $phoneAddresses
     * @return bool
     */
    protected function validatePhoneAddressPrefix(array $phoneAddresses): bool
    {
        Arr::map($phoneAddresses, function ($phoneAddress) {
            if (! Str::startsWith($phoneAddress, static::getPhoneAddressPrefix())) {
                throw new InvalidPhoneAddress('This phone number: '.$phoneAddress.' is wrongly formatted');
            }
        });

        return true;
    }

    /**
     * @param  array  $phoneAddresses
     * @return bool
     */
    protected function validatePhoneAddressLength(array $phoneAddresses): bool
    {
        Arr::map($phoneAddresses, function ($phoneAddress) {
            if (! Str::length($phoneAddress) === 12) {
                throw new InvalidPhoneAddress('This phone number: '.$phoneAddress.' is wrongly formatted');
            }
        });

        return true;
    }
}
