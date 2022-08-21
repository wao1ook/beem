<?php

namespace Emanate\BeemSms\Exceptions;

use Exception;
use Illuminate\Http\Response;

class InvalidBeemSecretKeyException extends Exception
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

    /**
     * Render the exception into an HTTP response.
     *
     * @return Response
     */
    public function render(): Response
    {
        return response('Your Beem Secret Key is wrongly set or missing.', 417);
    }
}
