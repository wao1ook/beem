<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Events;

use Emanate\BeemSms\InboundMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when Beem posts a mobile-originated message to your callback URL.
 */
class InboundSmsReceived
{
    use Dispatchable;
    use SerializesModels;

    /**
     * The inbound message.
     */
    public InboundMessage $message;

    public function __construct(InboundMessage $message)
    {
        $this->message = $message;
    }
}
