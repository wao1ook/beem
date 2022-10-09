<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Classes;

use Emanate\BeemSms\Exceptions\InvalidPhoneAddress;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class Validator
{
    /**
     * List of phone address prefixes.
     *
     * @var array<string>
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
     * @param  array<string>  $phoneAddresses
     *
     */
    public function validate(array $phoneAddresses): bool
    {
        return $this->validatePhoneAddressPrefix($phoneAddresses)
            && $this->validatePhoneAddressLength($phoneAddresses);
    }

    /**
     * @return array<string>
     *
     */
    protected static function getPhoneAddressPrefix(): array
    {
        return Validator::$phoneAddressPrefix;
    }

    /**
     * @param  array<string>  $phoneAddresses
     *
     */
    protected function validatePhoneAddressPrefix(array $phoneAddresses): bool
    {
        Arr::map($phoneAddresses, function ($phoneAddress): void {
            if (! Str::startsWith($phoneAddress, Validator::getPhoneAddressPrefix())) {
                throw new InvalidPhoneAddress('This phone number: '.$phoneAddress.' is wrongly formatted');
            }
        });

        return true;
    }

    /**
     * @param  array<string>  $phoneAddresses
     *
     */
    protected function validatePhoneAddressLength(array $phoneAddresses): bool
    {
        Arr::map($phoneAddresses, function ($phoneAddress): void {
            if (! (Str::length($phoneAddress) === 12)) {
                throw new InvalidPhoneAddress('This phone number: '.$phoneAddress.' is wrongly formatted');
            }
        });

        return true;
    }
}
