<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Http\Controllers;

use Emanate\BeemSms\Events\InboundSmsReceived;
use Emanate\BeemSms\InboundMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives mobile-originated messages posted by Beem.
 *
 * @see https://docs.beem.africa/guides/two-way-sms/inbound-callbacks
 */
class InboundSmsController
{
    /**
     * Handle an inbound message and acknowledge it.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $message = InboundMessage::fromPayload($request->json()->all());

        InboundSmsReceived::dispatch($message);

        return response()->json($message->acknowledgement());
    }
}
