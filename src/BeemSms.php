<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use Emanate\BeemSms\Contracts\Validator;
use Emanate\BeemSms\Exceptions\InvalidBeemApiKey;
use Emanate\BeemSms\Exceptions\InvalidBeemSecretKey;
use Emanate\BeemSms\Exceptions\InvalidBeemSenderName;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class BeemSms
{
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
    protected string $sendingSMSUrl;

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
        $this->sendingSMSUrl = config('beem.sending_sms_url', 'https://apisms.beem.africa/v1/send');
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
     * @throws GuzzleException
     */
    public function send(): ResponseInterface
    {
        return (new Client())->post(
            $this->sendingSMSUrl,
            [
                'verify' => false,
                'auth' => [$this->apiKey, $this->secretKey],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'source_addr' => $this->senderName,
                    'message' => $this->message,
                    'encoding' => 0,
                    'recipients' => $this->recipientAddress,
                ],
            ]
        );
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
     * @param  array<string>  $recipients
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
     * @param  array<string>  $recipients
     *
     * @return array<string>
     */
    protected function validateRecipientAddresses(array $recipients): array
    {
        if (config('beem.validate_phone_addresses')) {
            return app(Validator::class)
                ->new($recipients)
                ->validate();
        }

        return $recipients;
    }

    /**
     * @param  array<string>  $recipients
     * @return array<int<0, max>, array<string, int<0, 999999999>|string>>
     *
     * @throws Exception
     */
    protected function formatRecipientAddress(array $recipients): array
    {
        $recipient = [];

        foreach ($recipients as $eachRecipient) {
            $recipient[] = array_fill_keys(['dest_addr'], $eachRecipient);
        }

        $recipientAddress = [];

        foreach ($recipient as $singleRecipient) {
            $recipientAddress[] = array_merge(
                ['recipient_id' => random_int(00000000, 999999999)],
                $singleRecipient
            );
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
