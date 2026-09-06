<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use Emanate\BeemSms\Exceptions\InvalidBeemApiKey;
use Emanate\BeemSms\Exceptions\InvalidBeemSecretKey;
use Emanate\BeemSms\Exceptions\OtpException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Client for the Beem OTP API.
 *
 * It shares the SMS API key and secret, but lives on its own host and delivers
 * the PIN over SMS or WhatsApp depending on how the application is configured
 * in the Beem dashboard.
 *
 * @see https://docs.beem.africa/guides/otp/index
 */
class Otp
{
    /**
     * Response code returned when the OTP was submitted successfully.
     */
    public const CODE_SENT = 100;

    /**
     * Response code returned when the submitted PIN is correct.
     */
    public const CODE_VALID_PIN = 117;

    /**
     * API Key.
     */
    protected string $apiKey;

    /**
     * API Secret Key.
     */
    protected string $secretKey;

    /**
     * Access token, used instead of the API key and secret when present.
     */
    protected ?string $accessToken = null;

    /**
     * Base URL of the OTP API.
     */
    protected string $url;

    /**
     * The OTP application ID created in the Beem dashboard.
     */
    protected ?int $appId;

    /**
     * Any extra body parameters to merge into the next request.
     *
     * @var array<string, mixed>
     */
    protected array $parameters = [];

    /**
     * @throws InvalidBeemApiKey
     * @throws InvalidBeemSecretKey
     */
    public function __construct()
    {
        $accessToken = config('beem.access_token');

        $this->accessToken = is_string($accessToken) && $accessToken !== '' ? $accessToken : null;

        $this->checkForInvalidCredentials();

        $this->apiKey = (string) config('beem.api_key');
        $this->secretKey = (string) config('beem.secret_key');
        $this->url = config('beem.otp.url', 'https://apiotp.beem.africa/v1');

        $appId = config('beem.otp.app_id');

        $this->appId = $appId === null || $appId === '' ? null : (int) $appId;
    }

    public function apiKey(string $apiKey): Otp
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function secretKey(string $secretKey): Otp
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Authenticate with an access token instead of the API key and secret.
     */
    public function accessToken(string $accessToken): Otp
    {
        if ($accessToken === '') {
            throw new InvalidArgumentException('Access token must not be empty.');
        }

        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Override the OTP application ID for this request.
     */
    public function appId(int $appId): Otp
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * Set any extra body parameter, for anything not covered by a dedicated method.
     */
    public function parameter(string $name, mixed $value): Otp
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Request an OTP for a phone number, in international format without a `+`.
     *
     * @return array<string, mixed>
     *
     * @throws GuzzleException
     * @throws OtpException
     */
    public function request(string $phoneAddress): array
    {
        if ($this->appId === null) {
            throw new RuntimeException('An OTP application ID is required. Call appId() or set beem.otp.app_id.');
        }

        $phoneAddress = (string) preg_replace('/^\++/', '', $phoneAddress);

        if ($phoneAddress === '') {
            throw new InvalidArgumentException('A phone address is required.');
        }

        return $this->post('/request', [
            'appId' => $this->appId,
            'msisdn' => $phoneAddress,
        ]);
    }

    /**
     * Verify a PIN against the pinId returned by request().
     *
     * @return array<string, mixed>
     *
     * @throws GuzzleException
     * @throws OtpException
     */
    public function verify(string $pinId, string $pin): array
    {
        if ($pinId === '' || $pin === '') {
            throw new InvalidArgumentException('Both a pinId and a pin are required.');
        }

        return $this->post('/verify', [
            'pinId' => $pinId,
            'pin' => $pin,
        ]);
    }

    /**
     * Verify a PIN and return whether it was accepted, instead of throwing.
     *
     * @throws GuzzleException
     */
    public function check(string $pinId, string $pin): bool
    {
        try {
            $this->verify($pinId, $pin);

            return true;
        } catch (OtpException) {
            return false;
        }
    }

    /**
     * Post a body to the OTP API and return the decoded response.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     *
     * @throws GuzzleException
     * @throws OtpException
     */
    protected function post(string $path, array $body): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $options = [
            'verify' => config('beem.verify_ssl', true),
            'timeout' => (float) config('beem.timeout', 30),
            'http_errors' => false,
            'json' => array_merge($body, $this->parameters),
        ];

        if ($this->accessToken !== null) {
            $headers['Authorization'] = $this->accessToken;
        } else {
            $options['auth'] = [$this->apiKey, $this->secretKey];
        }

        $options['headers'] = $headers;

        $response = (new Client())->post($this->url . $path, $options);

        $decoded = json_decode($response->getBody()->getContents(), true);

        $decoded = is_array($decoded) ? $decoded : [];

        $code = (int) ($decoded['data']['message']['code'] ?? $decoded['code'] ?? 0);

        if ($response->getStatusCode() >= 400 || ! in_array($code, [self::CODE_SENT, self::CODE_VALID_PIN], true)) {
            throw OtpException::fromResponse($decoded);
        }

        return $decoded;
    }

    /**
     * @throws InvalidBeemApiKey
     * @throws InvalidBeemSecretKey
     */
    private function checkForInvalidCredentials(): void
    {
        if ($this->accessToken !== null) {
            return;
        }

        match (true) {
            config('beem.api_key') === null || config('beem.api_key') === '' => throw new InvalidBeemApiKey(),
            config('beem.secret_key') === null || config('beem.secret_key') === '' => throw new InvalidBeemSecretKey(),
            default => $this,
        };
    }
}
