<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Tests\Unit;

use Emanate\BeemSms\Exceptions\InvalidPhoneAddress;
use Emanate\BeemSms\Tests\TestCase;

class DefaultValidatorTest extends TestCase
{
    /**
     * @return void
     * @throws InvalidPhoneAddress
     */
    public function testPhoneAddressesPassesValidationWhenValid(): void
    {
        $phoneAddresses = [
            '255714000000',
            '255734000000',
        ];

        $this->assertSame(
            $phoneAddresses,
            $this->getDefaultValidator()->new($phoneAddresses)->validate()
        );
    }

    /**
     * @return void
     * @throws InvalidPhoneAddress
     */
    public function testPhoneAddressesGetFixedWhileBeingValidated()
    {
        $wronglyFormattedPhoneAddresses = [
            '0714000000',
            '0734000000',
            '+255744000000',
        ];

        $this->assertSame(
            [
                '255714000000',
                '255734000000',
                '255744000000'
            ],
            $this->getDefaultValidator()->new($wronglyFormattedPhoneAddresses)->validate()
        );
    }

    public function testValidatorWillThrowAnExceptionWhenPhoneAddressIsInvalid(): void
    {
        $phoneAddresses = [
            '25571400000',
            '0573400000',
        ];

        $this->expectException(InvalidPhoneAddress::class);

        $this->getDefaultValidator()->new($phoneAddresses)->validate();
    }
}
