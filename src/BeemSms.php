<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use DateTimeInterface;
use DateTimeZone;
use Emanate\BeemSms\Contracts\Validator;
use Emanate\BeemSms\Exceptions\BeemApiException;
use Emanate\BeemSms\Exceptions\InvalidBeemApiKey;
use Emanate\BeemSms\Exceptions\InvalidBeemSecretKey;
use Emanate\BeemSms\Exceptions\InvalidBeemSenderName;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class BeemSms
{
    /**
     * Default GSM encoding.
     */
    public const ENCODING_GSM = 0;

    /**
     * Unicode (UCS-2) encoding, for messages with non-GSM characters.
     */
    public const ENCODING_UNICODE = 8;

    /**
     * Maximum recipients Beem accepts in a single send request.
     *
     * @see https://docs.beem.africa/guides/sms/api-responses (code 114)
     */
    public const MAX_RECIPIENTS = 1000;

    /**
     * Maximum length of an alphanumeric sender ID.
     */
    public const MAX_SENDER_NAME_LENGTH = 11;

    /**
     * The format Beem expects for a scheduled send time (GMT+0).
     */
    public const SCHEDULE_TIME_FORMAT = 'Y-m-d H:i';

    /**
     * API Key
     */
    protected string $apiKey;

    /**
     * API Secret Key
     */
    protected string $secretKey;

    /**
     * Sender Name registered by Beem
     */
    protected string $senderName;

    /**
     * The message to be sent.
     */
    protected string $message;

    /**
     * Beem Sms Sending SMS URL
     */
    protected string $url;

    /**
     * Base URL for the public endpoints: balance, sender names and templates.
     */
    protected string $publicUrl;

    /**
     * Base URL for the delivery reports API.
     */
    protected string $deliveryReportsUrl;

    /**
     * Message encoding sent as the `encoding` field.
     */
    protected int $encoding;

    /**
     * Optional scheduled send time, in GMT+0, formatted as `Y-m-d H:i`.
     */
    protected ?string $scheduleTime = null;

    /**
     * Optional job name, used by Beem for tracking.
     */
    protected ?string $jobName = null;

    /**
     * Optional campaign title, used by Beem for tracking.
     */
    protected ?string $campaignTitle = null;

    /**
     * Array of phone addresses
     *
     * @var array<int<0, max>, array<string, int<0, 999999999>|string>>
     */
    protected array $recipientAddress;

    /**
     * @throws InvalidBeemApiKey
     * @throws InvalidBeemSecretKey
     * @throws InvalidBeemSenderName
     */
    public function __construct()
    {
        $this->checkForInvalidCredentials();

        $this->apiKey = config('beem.api_key');
        $this->secretKey = config('beem.secret_key');
        $this->senderName = config('beem.sender_name');
        $this->url = config('beem.sending_sms_url', 'https://apisms.beem.africa/v1');
        $this->publicUrl = config('beem.public_api_url', 'https://apisms.beem.africa/public/v1');
        $this->deliveryReportsUrl = config('beem.delivery_reports_url', 'https://dlrapi.beem.africa/public/v1');
        $this->encoding = (int) config('beem.encoding', self::ENCODING_GSM);
    }

    public function apiKey(string $apiKey): BeemSms
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function secretKey(string $secretKey): BeemSms
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Override the registered sender ID for this message.
     */
    public function senderName(string $senderName): BeemSms
    {
        if ($senderName === '' || mb_strlen($senderName) > self::MAX_SENDER_NAME_LENGTH) {
            throw new InvalidArgumentException(
                'Sender name must be between 1 and ' . self::MAX_SENDER_NAME_LENGTH . ' characters.'
            );
        }

        $this->senderName = $senderName;

        return $this;
    }

    /**
     * Set the message encoding. Use self::ENCODING_UNICODE for non-GSM characters.
     */
    public function encoding(int $encoding): BeemSms
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Send the message using unicode (UCS-2) encoding.
     */
    public function unicode(): BeemSms
    {
        return $this->encoding(self::ENCODING_UNICODE);
    }

    /**
     * Schedule the message. Beem expects GMT+0, in `Y-m-d H:i` format.
     */
    public function scheduleTime(DateTimeInterface|string $scheduleTime): BeemSms
    {
        if ($scheduleTime instanceof DateTimeInterface) {
            $this->scheduleTime = date_create_immutable('@' . $scheduleTime->getTimestamp())
                ->setTimezone(new DateTimeZone('UTC'))
                ->format(self::SCHEDULE_TIME_FORMAT);

            return $this;
        }

        $this->scheduleTime = $scheduleTime;

        return $this;
    }

    /**
     * Alias of scheduleTime().
     */
    public function scheduleAt(DateTimeInterface|string $scheduleTime): BeemSms
    {
        return $this->scheduleTime($scheduleTime);
    }

    /**
     * Tag the request with a job name, for tracking in the Beem dashboard.
     */
    public function jobName(string $jobName): BeemSms
    {
        $this->jobName = $jobName;

        return $this;
    }

    /**
     * Tag the request with a campaign title, for tracking in the Beem dashboard.
     */
    public function campaignTitle(string $campaignTitle): BeemSms
    {
        $this->campaignTitle = $campaignTitle;

        return $this;
    }

    /**
     * Fetch the remaining SMS credit balance.
     *
     * @throws BeemApiException
     * @throws GuzzleException
     */
    public function balance(): mixed
    {
        try {
            $body = $this->request('GET', $this->publicUrl . '/vendors/balance');

            return $body['data']['credit_balance'] ?? null;

        } catch (GuzzleException $e) {
            Log::error('Failed to fetch SMS Balance: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function send(): ResponseInterface
    {
        return (new Client())->post(
            $this->url . '/send',
            [
                'verify' => config('beem.verify_ssl', true),
                'timeout' => (float) config('beem.timeout', 30),
                'auth' => [$this->apiKey, $this->secretKey],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $this->payload(),
            ]
        );
    }

    /**
     * Send the message and return the decoded body, throwing on a non-100 response code.
     *
     * @return array<string, mixed>
     *
     * @throws BeemApiException
     * @throws GuzzleException
     */
    public function sendAndParse(): array
    {
        $body = json_decode($this->send()->getBody()->getContents(), true);

        if ( ! is_array($body)) {
            throw new BeemApiException('Unexpected response body returned by Beem.');
        }

        if (isset($body['code']) && (int) $body['code'] !== 100) {
            throw BeemApiException::fromResponse($body);
        }

        return $body;
    }

    /**
     * The request body that will be sent to the Beem send endpoint.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $payload = [
            'source_addr' => $this->senderName,
            'message' => $this->message,
            'encoding' => $this->encoding,
            'recipients' => $this->recipientAddress,
        ];

        if ($this->scheduleTime !== null) {
            $payload['schedule_time'] = $this->scheduleTime;
        }

        if ($this->jobName !== null) {
            $payload['job_name'] = $this->jobName;
        }

        if ($this->campaignTitle !== null) {
            $payload['campaign_title'] = $this->campaignTitle;
        }

        return $payload;
    }

    /**
     * Fetch the delivery report for a single recipient of a previous send.
     *
     * Beem needs at least 5 minutes after submitting before a report is meaningful.
     *
     * @return array<string, mixed>
     *
     * @throws BeemApiException
     * @throws GuzzleException
     */
    public function deliveryReport(string $destinationAddress, string $requestId): array
    {
        return $this->request('GET', $this->deliveryReportsUrl . '/delivery-reports', [
            'query' => [
                'dest_addr' => $destinationAddress,
                'request_id' => $requestId,
            ],
        ]);
    }

    /**
     * List the sender names registered on the account.
     *
     * @param  array<string, mixed>  $filters  page, limit, sortBy, sortOrder, q, status
     * @return array<string, mixed>
     *
     * @throws BeemApiException
     * @throws GuzzleException
     */
    public function senderNames(array $filters = []): array
    {
        return $this->request('GET', $this->publicUrl . '/sender-names', [
            'query' => $filters,
        ]);
    }

    /**
     * List the SMS templates on the account.
     *
     * @param  array<string, mixed>  $filters  page, limit, sortBy, sortOrder
     * @return array<string, mixed>
     *
     * @throws BeemApiException
     * @throws GuzzleException
     */
    public function templates(array $filters = []): array
    {
        return $this->request('GET', $this->publicUrl . '/sms-templates', [
            'query' => $filters,
        ]);
    }

    /**
     * Create an SMS template.
     *
     * @return array<string, mixed>
     *
     * @throws BeemApiException
     * @throws GuzzleException
     */
    public function createTemplate(string $title, string $message): array
    {
        return $this->request('POST', $this->publicUrl . '/sms-templates', [
            'json' => [
                'sms_title' => $title,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Update an existing SMS template.
     *
     * @return array<string, mixed>
     *
     * @throws BeemApiException
     * @throws GuzzleException
     */
    public function updateTemplate(string $templateId, string $title, string $message): array
    {
        return $this->request('PUT', $this->publicUrl . '/sms-templates/' . rawurlencode($templateId), [
            'json' => [
                'sms_title' => $title,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Delete an SMS template.
     *
     * @return array<string, mixed>
     *
     * @throws BeemApiException
     * @throws GuzzleException
     */
    public function deleteTemplate(string $templateId): array
    {
        return $this->request('DELETE', $this->publicUrl . '/sms-templates/' . rawurlencode($templateId));
    }

    /**
     * @throws Exception
     */
    public function loadRecipients(mixed $collection, string $column = 'phone_number'): BeemSms
    {
        $recipients = $collection->map(fn ($item) => $item[$column])->toArray();

        return $this->getRecipients($recipients);
    }

    /**
     * @param array<string> $recipients
     *
     * @throws Exception
     */
    public function getRecipients(array $recipients): BeemSms
    {
        if (count($recipients) === 0) {
            throw new RuntimeException('Recipients should not be empty');
        }

        $recipients = $this->validateRecipientAddresses($recipients);

        $this->recipientAddress = $this->formatRecipientAddress($recipients);

        return $this;
    }

    /**
     * @throws Exception
     *
     * @phpstan-ignore-next-line
     */
    public function unpackRecipients(...$recipients): BeemSms
    {
        if (count($recipients) === 0) {
            throw new RuntimeException('Recipients should not be empty');
        }

        $recipients = $this->validateRecipientAddresses($recipients);

        $this->recipientAddress = $this->formatRecipientAddress($recipients);

        return $this;
    }

    public function content(string $message): BeemSms
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Perform an authenticated request and return the decoded body.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     *
     * @throws BeemApiException
     * @throws GuzzleException
     */
    protected function request(string $method, string $url, array $options = []): array
    {
        $options = array_merge([
            'verify' => config('beem.verify_ssl', true),
            'timeout' => (float) config('beem.timeout', 30),
            'auth' => [$this->apiKey, $this->secretKey],
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ], $options);

        $response = (new Client())->request($method, $url, $options);

        $body = json_decode($response->getBody()->getContents(), true);

        $body = is_array($body) ? $body : [];

        if ($response->getStatusCode() >= 400) {
            throw BeemApiException::fromResponse($body);
        }

        return $body;
    }

    /**
     * @param array<string> $recipients
     *
     * @return array<string>
     */
    protected function validateRecipientAddresses(array $recipients): array
    {
        if (count($recipients) > self::MAX_RECIPIENTS) {
            throw new RuntimeException(
                'Recipients exceed the maximum limit of ' . self::MAX_RECIPIENTS . ' per request.'
            );
        }

        if (config('beem.validate_phone_addresses')) {
            return app(Validator::class)
                ->new($recipients)
                ->validate();
        }

        return $recipients;
    }

    /**
     * @param array<string> $recipients
     * @return array<int<0, max>, array<string, int<0, 999999999>|string>>
     *
     * @throws Exception
     */
    protected function formatRecipientAddress(array $recipients): array
    {
        $recipientAddress = [];

        $recipientId = 1;

        foreach ($recipients as $eachRecipient) {
            $recipientAddress[] = [
                'recipient_id' => $recipientId++,
                'dest_addr' => $eachRecipient,
            ];
        }

        return $recipientAddress;
    }

    /**
     * @return void
     * @throws InvalidBeemApiKey
     * @throws InvalidBeemSecretKey
     * @throws InvalidBeemSenderName
     */
    private function checkForInvalidCredentials(): void
    {
        match (true) {
            config('beem.api_key') === null || config('beem.api_key') === '' => throw new InvalidBeemApiKey(),
            config('beem.secret_key') === null || config('beem.secret_key') === '' => throw new InvalidBeemSecretKey(),
            config('beem.sender_name') === null || config('beem.sender_name') === '' => throw new InvalidBeemSenderName(),
            default => $this,
        };
    }
}
