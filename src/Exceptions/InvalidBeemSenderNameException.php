<?php

namespace Emanate\BeemSms\Exceptions;

use Exception;

class InvalidBeemSenderNameException extends Exception
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
        return response('Your Sender Name is wrongly set or missing.', 417);
    }
}
