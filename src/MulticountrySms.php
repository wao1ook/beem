<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use Emanate\BeemSms\Exceptions\InvalidMulticountryCredentials;
use Emanate\BeemSms\Exceptions\MulticountrySmsException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Client for the Beem Multicountry SMS platform.
 *
 * This is a separate platform from the Beem SMS API: it lives on its own host,
 * authenticates with a username and password rather than an API key and secret,
 * and takes uppercase form-encoded parameters.
 *
 * @see https://docs.beem.africa/guides/multicountry-sms/index
 */
class MulticountrySms
{
    /**
     * GSM 7-bit text, the platform default.
     */
    public const CHARCODE_GSM = 0;

    /**
     * Binary (8-bit). The message must be hex encoded.
     */
    public const CHARCODE_BINARY = 2;

    /**
     * Type of Number: unknown.
     */
    public const TON_UNKNOWN = 0;

    /**
     * Type of Number: international. The platform default.
     */
    public const TON_INTERNATIONAL = 1;

    /**
     * Type of Number: alphanumeric. Required for alphanumeric sender IDs.
     */
    public const TON_ALPHANUMERIC = 5;

    /**
     * Maximum length of an alphanumeric sender address.
     */
    public const MAX_SOURCE_ADDRESS_LENGTH = 11;

    /**
     * Platform status code meaning the request succeeded.
     */
    public const STATUS_SUCCESS = 0;

    /**
     * Account username.
     */
    protected string $username;

    /**
     * Account password.
     */
    protected string $password;

    /**
     * Full URL of the send endpoint.
     */
    protected string $sendUrl;

    /**
     * Full URL of the balance endpoint.
     */
    protected string $balanceUrl;

    /**
     * Sender address (SOURCEADDR).
     */
    protected ?string $sourceAddress = null;

    /**
     * Destination address (DESTADDR).
     */
    protected ?string $destinationAddress = null;

    /**
     * Message body (MESSAGE).
     */
    protected ?string $message = null;

    /**
     * Any additional platform parameters, keyed by their uppercase name.
     *
     * @var array<string, scalar>
     */
    protected array $parameters = [];

    /**
     * @throws InvalidMulticountryCredentials
     */
    public function __construct()
    {
        $this->checkForInvalidCredentials();

        $this->username = (string) config('beem.multicountry.username');
        $this->password = (string) config('beem.multicountry.password');
        $this->sendUrl = config('beem.multicountry.send_url', 'https://api.blsmsgw.com:8443/bin/send.json');
        $this->balanceUrl = config(
            'beem.multicountry.balance_url',
            'https://www.blsmsgw.com/portal/api/userAccountBalance'
        );

        $sourceAddress = config('beem.multicountry.source_address');

        if (is_string($sourceAddress) && $sourceAddress !== '') {
            $this->from($sourceAddress);
        }
    }

    /**
     * Override the account username for this request.
     */
    public function username(string $username): MulticountrySms
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Override the account password for this request.
     */
    public function password(string $password): MulticountrySms
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set the sender address (SOURCEADDR).
     *
     * Alphanumeric senders are limited to 11 GSM characters and require
     * SOURCEADDRTON=5, which is applied automatically.
     */
    public function from(string $sourceAddress): MulticountrySms
    {
        if ($sourceAddress === '') {
            throw new InvalidArgumentException('Source address must not be empty.');
        }

        if ( ! ctype_digit($sourceAddress)) {
            if (mb_strlen($sourceAddress) > self::MAX_SOURCE_ADDRESS_LENGTH) {
                throw new InvalidArgumentException(
                    'Alphanumeric source address must not exceed ' . self::MAX_SOURCE_ADDRESS_LENGTH . ' characters.'
                );
            }

            $this->parameters['SOURCEADDRTON'] = self::TON_ALPHANUMERIC;
        }

        $this->sourceAddress = $sourceAddress;

        return $this;
    }

    /**
     * Set the destination address (DESTADDR), in international format without a `+`.
     */
    public function to(string $destinationAddress): MulticountrySms
    {
        // Not ltrim(), which Pint's mb_str_functions rule rewrites to the PHP 8.4-only mb_ltrim().
        $destinationAddress = (string) preg_replace('/^\++/', '', $destinationAddress);

        if ($destinationAddress === '') {
            throw new InvalidArgumentException('Destination address must not be empty.');
        }

        $this->destinationAddress = $destinationAddress;

        return $this;
    }

    /**
     * Set the message body (MESSAGE).
     */
    public function content(string $message): MulticountrySms
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Request a delivery report, optionally to a specific HTTPS callback URL.
     */
    public function deliveryReport(?string $callbackUrl = null): MulticountrySms
    {
        $this->parameters['DLR'] = 1;

        if ($callbackUrl !== null) {
            $this->parameters['DLRADDRESS'] = $callbackUrl;
        }

        return $this;
    }

    /**
     * Send the message as binary (8-bit). The body must already be hex encoded.
     */
    public function binary(): MulticountrySms
    {
        $this->parameters['CHARCODE'] = self::CHARCODE_BINARY;

        return $this;
    }

    /**
     * Set the validity period, in seconds, before the message expires.
     */
    public function validityPeriod(int $seconds): MulticountrySms
    {
        $this->parameters['VP'] = $seconds;

        return $this;
    }

    /**
     * Set the source and destination ports, added to the UDH.
     */
    public function ports(int $sourcePort, int $destinationPort): MulticountrySms
    {
        foreach (['SOURCEPORT' => $sourcePort, 'DESTPORT' => $destinationPort] as $name => $port) {
            if ($port < 0 || $port > 65535) {
                throw new InvalidArgumentException($name . ' must be between 0 and 65535.');
            }

            $this->parameters[$name] = $port;
        }

        return $this;
    }

    /**
     * Attach a User Data Header, in hex, and flag its presence.
     */
    public function userDataHeader(string $header): MulticountrySms
    {
        $this->parameters['UDHI'] = 1;
        $this->parameters['UDH'] = $header;

        return $this;
    }

    /**
     * Mark this message as one part of a concatenated message.
     */
    public function concatenated(int $reference, int $sequence, int $total): MulticountrySms
    {
        if ($reference < 0 || $reference > 255) {
            throw new InvalidArgumentException('CONCATSMSREF must be between 0 and 255.');
        }

        $this->parameters['CONCATSMSREF'] = $reference;
        $this->parameters['CONCATSMSSEQ'] = $sequence;
        $this->parameters['CONCATSMSMAX'] = $total;

        return $this;
    }

    /**
     * Set any platform parameter directly, for anything not covered above.
     */
    public function parameter(string $name, string|int|float|bool $value): MulticountrySms
    {
        $this->parameters[mb_strtoupper($name)] = $value;

        return $this;
    }

    /**
     * The form parameters that will be posted to the platform.
     *
     * @return array<string, scalar>
     */
    public function payload(): array
    {
        if ($this->sourceAddress === null) {
            throw new RuntimeException('A source address is required. Call from() or set beem.multicountry.source_address.');
        }

        if ($this->destinationAddress === null) {
            throw new RuntimeException('A destination address is required. Call to().');
        }

        if ($this->message === null) {
            throw new RuntimeException('A message is required. Call content().');
        }

        return array_merge($this->parameters, [
            'USERNAME' => $this->username,
            'PASSWORD' => $this->password,
            'SOURCEADDR' => $this->sourceAddress,
            'DESTADDR' => $this->destinationAddress,
            'MESSAGE' => $this->message,
        ]);
    }

    /**
     * Send the message.
     *
     * The platform answers HTTP 200 even on failure, so the status inside the
     * body is checked and a non-zero status is raised as an exception.
     *
     * @return array<string, mixed>
     *
     * @throws GuzzleException
     * @throws MulticountrySmsException
     */
    public function send(): array
    {
        $response = (new Client())->post($this->sendUrl, [
            'verify' => config('beem.verify_ssl', true),
            'timeout' => (float) config('beem.timeout', 30),
            'http_errors' => false,
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->payload(),
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $body = is_array($body) ? $body : [];

        if ($response->getStatusCode() >= 400) {
            throw MulticountrySmsException::fromResponse($body);
        }

        foreach ($body['results'] ?? [] as $result) {
            if ((int) ($result['status'] ?? 1) !== self::STATUS_SUCCESS) {
                throw MulticountrySmsException::fromResponse(['results' => [$result]]);
            }
        }

        return $body;
    }

    /**
     * Fetch the account balance from the portal API.
     *
     * @return array<string, mixed>
     *
     * @throws GuzzleException
     * @throws MulticountrySmsException
     */
    public function balance(): array
    {
        $response = (new Client())->get($this->balanceUrl, [
            'verify' => config('beem.verify_ssl', true),
            'timeout' => (float) config('beem.timeout', 30),
            'http_errors' => false,
            'auth' => [$this->username, $this->password],
            'headers' => ['Accept' => 'application/json'],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $body = is_array($body) ? $body : [];

        if ($response->getStatusCode() >= 400) {
            throw MulticountrySmsException::fromResponse($body);
        }

        return $body;
    }

    /**
     * @throws InvalidMulticountryCredentials
     */
    private function checkForInvalidCredentials(): void
    {
        $username = config('beem.multicountry.username');
        $password = config('beem.multicountry.password');

        if ($username === null || $username === '') {
            throw new InvalidMulticountryCredentials('A Multicountry SMS username is required.');
        }

        if ($password === null || $password === '') {
            throw new InvalidMulticountryCredentials('A Multicountry SMS password is required.');
        }
    }
}
