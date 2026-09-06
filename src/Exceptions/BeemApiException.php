<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when the Beem API answers with a non-success response code.
 *
 * @see https://docs.beem.africa/guides/sms/api-responses
 */
class BeemApiException extends Exception
{
    /**
     * Beem response codes and their documented meaning.
     *
     * @var array<int, string>
     */
    public const RESPONSE_CODES = [
        100 => 'Request successful. Message has been submitted for processing.',
        101 => 'Invalid phone number (dest_addr is invalid).',
        102 => 'Insufficient balance to send SMS.',
        103 => 'Network timeout or internal server error.',
        104 => 'Missing required parameters.',
        109 => 'Invalid or empty message content.',
        110 => 'Message contains unsupported special characters.',
        111 => 'Invalid Sender ID (not registered or inactive).',
        112 => 'Scheduled time is in the past.',
        113 => 'Invalid request format (JSON/XML).',
        114 => 'Recipients exceed the maximum limit (1000).',
        115 => 'Destination address is missing.',
        116 => 'Missing or invalid reference ID.',
        117 => 'Invalid scheduled time format (YYYY-MM-DD HH:MM).',
        118 => 'Missing encoding type.',
        119 => 'Missing recipients tag.',
        120 => 'Missing or invalid authentication parameters.',
        121 => 'Sender ID format is invalid.',
        122 => 'Duplicate Sender ID.',
        123 => 'Template does not exist.',
        127 => 'SMS template delete failed.',
    ];

    /**
     * The decoded response body returned by Beem, when available.
     *
     * @var array<string, mixed>
     */
    protected array $response;

    /**
     * @param  array<string, mixed>  $response
     */
    public function __construct(string $message = '', int $code = 0, array $response = [], ?Throwable $previous = null)
    {
        $this->response = $response;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Build the exception from a decoded Beem response body.
     *
     * @param  array<string, mixed>  $response
     */
    public static function fromResponse(array $response): self
    {
        $code = (int) ($response['code'] ?? 0);

        $message = $response['message'] ?? self::RESPONSE_CODES[$code] ?? 'Unknown Beem API error.';

        return new self(is_string($message) ? $message : 'Unknown Beem API error.', $code, $response);
    }

    /**
     * The decoded response body returned by Beem.
     *
     * @return array<string, mixed>
     */
    public function response(): array
    {
        return $this->response;
    }

    /**
     * The documented description for the Beem response code.
     */
    public function description(): string
    {
        return self::RESPONSE_CODES[$this->getCode()] ?? $this->getMessage();
    }
}
