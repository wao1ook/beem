<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when the Beem OTP API answers with a non-success response code.
 *
 * @see https://docs.beem.africa/guides/otp/api-responses
 */
class OtpException extends Exception
{
    /**
     * OTP response codes and their documented meaning.
     *
     * @var array<int, string>
     */
    public const RESPONSE_CODES = [
        100 => 'SMS sent successfully - OTP message has been submitted.',
        101 => 'Failed to send SMS - failed to send the OTP PIN generated.',
        102 => 'Invalid phone number - invalid MSISDN.',
        103 => 'Phone number missing - MSISDN parameter is missing.',
        104 => 'Application ID missing - application ID parameter is missing.',
        106 => 'Application not found.',
        107 => 'Application is inactive - application status is inactive.',
        108 => 'No channel found - channel is not set for the application.',
        109 => 'Placeholder not found - template definition does not contain a placeholder.',
        110 => 'Username or Password missing - credentials for sending OTP SMS are missing.',
        111 => 'PIN missing - PIN parameter is missing.',
        112 => 'pinId missing - pinId parameter is missing.',
        113 => 'pinId not found - pinId is inactive or incorrect.',
        114 => 'Incorrect PIN - PIN sent is not correct.',
        115 => 'PIN timeout - PIN has expired.',
        116 => 'Attempts exceeded - PIN verification attempts have exceeded the limit.',
        117 => 'Valid PIN - PIN is correct.',
        118 => 'Duplicate PIN - PIN is used again.',
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
     * Build the exception from a decoded OTP response body.
     *
     * @param  array<string, mixed>  $response
     */
    public static function fromResponse(array $response): self
    {
        $result = $response['data']['message'] ?? $response;

        $code = (int) ($result['code'] ?? 0);

        $message = $result['message'] ?? self::RESPONSE_CODES[$code] ?? 'Unknown Beem OTP error.';

        return new self(is_string($message) ? $message : 'Unknown Beem OTP error.', $code, $response);
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
     * The documented description for the OTP response code.
     */
    public function description(): string
    {
        return self::RESPONSE_CODES[$this->getCode()] ?? $this->getMessage();
    }
}
