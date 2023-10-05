<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Contracts;

interface Validator
{
    /**
     * Create a new Validator instance.
     */
    public function new(array $phoneAddresses): Validator;

    /**
     * Run the validator's logic against the phone addresses.
     */
    public function validate(): array;
}
