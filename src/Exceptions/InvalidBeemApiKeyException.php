<?php

namespace Emanate\BeemSms\Exceptions;

use Exception;

class InvalidBeemApiKeyException extends Exception
{
    /**
     * Report the exception
     *
     * @return bool
     */
    public function report(): bool
    {
        return true;
    }


    public function render()
    {
        return response('Your Beem Api Key is wrongly set or missing.', 417);
    }
}
