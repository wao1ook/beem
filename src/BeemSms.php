<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use Emanate\BeemSms\Exceptions\InvalidBeemApiKey;
use Emanate\BeemSms\Exceptions\InvalidBeemSecretKey;
use Emanate\BeemSms\Exceptions\InvalidBeemSenderName;
use Emanate\BeemSms\Exceptions\InvalidPhoneAddress;
use Emanate\BeemSms\Contracts\Validator;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class BeemSms
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
     * Beem Sms Base URL
     */
    protected string $url;

    /**
     * Array of phone addresses
     *
     * @var array<string, string>
     */
    protected array $recipientAddress;

    /**
     * @throws InvalidBeemApiKey
     * @throws InvalidBeemSecretKey
     * @throws InvalidBeemSenderName
     */
    public function __construct()
    {
        if ($this->isApiKeyEmpty()) {
            throw new InvalidBeemApiKey();
        }

        if ($this->isSecretKeyEmpty()) {
            throw new InvalidBeemSecretKey();
        }

        if ($this->isSenderNameEmpty()) {
            throw new InvalidBeemSenderName();
        }

        $this->apiKey = config('beem.api_key');
        $this->secretKey = config('beem.secret_key');
        $this->senderName = config('beem.sender_name');
        $this->url = 'https://apisms.beem.africa/v1/send';
    }

    private function isApiKeyEmpty(): bool
    {
        return config('beem.api_key') === null || config('beem.api_key') === '';
    }

    private function isSecretKeyEmpty(): bool
    {
        return config('beem.secret_key') === null || config('beem.secret_key') === '';
    }

    private function isSenderNameEmpty(): bool
    {
        return config('beem.sender_name') === null || config('beem.sender_name') === '';
    }

    public function apiKey(string $apiKey = ''): BeemSms
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function secretKey(string $secretKey = ''): BeemSms
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
            $this->url,
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

    /**
     * @param  array<string>  $recipients
     *
     * @throws InvalidPhoneAddress
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
     * @return array<string>
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

    public function content(string $message = ''): BeemSms
    {
        $this->message = $message;

        return $this;
    }
}
