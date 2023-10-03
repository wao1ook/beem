<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use Emanate\BeemSms\Contracts\Validator as ValidatorContract;
use Emanate\BeemSms\Exceptions\InvalidPhoneAddress;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class Validator implements ValidatorContract
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
        '25577',
        '25578',
    ];

    private array $phoneAddresses;

    public function new(array $phoneAddresses): Validator
    {
        $this->phoneAddresses = $phoneAddresses;

        return $this;
    }

    /**
     * @throws InvalidPhoneAddress
     */
    public function validate(): array
    {
        $this->fixIfPhoneAddressStartsWithZeroOrPlus();

        $this->validatePhoneAddressPrefix();

        $this->validatePhoneAddressLength();

        return $this->phoneAddresses;
    }

    /**
     * @return array<string>
     */
    protected static function getPhoneAddressPrefix(): array
    {
        return self::$phoneAddressPrefix;
    }

    protected function fixIfPhoneAddressStartsWithZeroOrPlus(): Validator
    {
        $this->phoneAddresses = Arr::map($this->phoneAddresses, static function ($phoneAddress) {
            if (Str::startsWith($phoneAddress, ['07', '06'])) {
                return Str::replaceFirst('0', '255', $phoneAddress);
            }

            if (Str::startsWith($phoneAddress, '+')) {
                return Str::remove('+', $phoneAddress);
            }

            return $phoneAddress;
        });

        return $this;
    }

    /**
     * @throws InvalidPhoneAddress
     */
    protected function validatePhoneAddressPrefix(): void
    {
        $wronglyFormattedPhoneAddresses = array_filter($this->phoneAddresses, static function ($phoneAddress) {
            if (! Str::startsWith($phoneAddress, self::getPhoneAddressPrefix())) {
                return $phoneAddress;
            }

            return null;
        });

        if (empty($wronglyFormattedPhoneAddresses)) {
            return;
        }

        throw new InvalidPhoneAddress(
            'Phone '.Str::plural('address', array_count_values($wronglyFormattedPhoneAddresses)).': '.implode(', ', $wronglyFormattedPhoneAddresses).' wrongly formatted'
        );
    }

    /**
     * @throws InvalidPhoneAddress
     */
    protected function validatePhoneAddressLength(): void
    {
        $wronglyFormattedPhoneAddresses = array_filter($this->phoneAddresses, static function ($phoneAddress) {
            if (! (Str::length($phoneAddress) === 12)) {
                return $phoneAddress;
            }

            return null;
        });

        if (empty($wronglyFormattedPhoneAddresses)) {
            return;
        }

        throw new InvalidPhoneAddress(
            'Phone '.Str::plural('address', array_count_values($wronglyFormattedPhoneAddresses)).': '.implode(', ', $wronglyFormattedPhoneAddresses).' wrongly formatted'
        );
    }
}