<?php

namespace Emanate\BeemSms\Contracts;

interface Validator
{
    /**
     * Create a new Validator instance.
     *
     * @param  array  $phoneAddresses
     * @return Validator
     */
    public function new(array $phoneAddresses): Validator;

    /**
     * Run the validator's logic against the phone addresses.
     */
    public function validate(): array;
}
