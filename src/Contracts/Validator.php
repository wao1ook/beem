<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Contracts;

interface Validator
{
    /**
     * Create a new Validator instance.
     *
     * @param  array<string>  $phoneAddresses
     */
    public function new(array $phoneAddresses): Validator;

    /**
     * Run the validator's logic against the phone addresses.
     *
     * @return array<string>
     */
    public function validate(): array;
}
