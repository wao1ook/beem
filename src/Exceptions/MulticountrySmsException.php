<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when the Multicountry SMS platform answers with a non-zero status code.
 *
 * The platform returns HTTP 200 even for failures, so the status inside the body
 * is the only reliable signal.
 *
 * @see https://docs.beem.africa/guides/multicountry-sms/api-responses
 */
class MulticountrySmsException extends Exception
{
    /**
     * Platform status codes and their documented meaning.
     *
     * @var array<int, string>
     */
    public const STATUS_CODES = [
        0 => 'Success',
        1 => 'Unknown error',
        2 => 'Syntax error / missing mandatory parameter',
        10 => 'Access denied',
        11 => 'Invalid message',
        16 => 'No message credits left',
    ];

    /**
     * The decoded response body returned by the platform, when available.
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
     * Build the exception from a decoded platform response body.
     *
     * @param  array<string, mixed>  $response
     */
    public static function fromResponse(array $response): self
    {
        $result = $response['results'][0] ?? [];

        $status = (int) ($result['status'] ?? $response['status'] ?? 1);

        $message = $result['statustext']
            ?? $response['statustext']
            ?? $response['error']
            ?? self::STATUS_CODES[$status]
            ?? 'Unknown Multicountry SMS error.';

        return new self(
            is_string($message) ? $message : 'Unknown Multicountry SMS error.',
            $status,
            $response
        );
    }

    /**
     * The decoded response body returned by the platform.
     *
     * @return array<string, mixed>
     */
    public function response(): array
    {
        return $this->response;
    }

    /**
     * The documented description for the platform status code.
     */
    public function description(): string
    {
        return self::STATUS_CODES[$this->getCode()] ?? $this->getMessage();
    }
}
