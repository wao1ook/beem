<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Exceptions;

use Exception;
use Illuminate\Http\Response;

final class InvalidBeemSenderName extends Exception
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
        return response('Your Sender Name is wrongly set or missing.', 417);
    }
}
