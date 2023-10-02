<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Exceptions;

use Exception;
use Illuminate\Http\Response;

final class InvalidBeemSecretKey extends Exception
{
    /**
     * Report the exception
     */
    public function report(): bool
    {
        return true;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): Response
    {
        return response('Your Beem Secret Key is wrongly set or missing.', 417);
    }
}
