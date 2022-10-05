<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use Emanate\BeemSms\Classes\Validator;
use Emanate\BeemSms\Exceptions\InvalidBeemApiKey;
use Emanate\BeemSms\Exceptions\InvalidBeemSecretKey;
use Emanate\BeemSms\Exceptions\InvalidBeemSenderName;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

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

    protected string $url;

    protected array $recipientAddress;

    /**
     * @throws InvalidBeemApiKey
     * @throws InvalidBeemSecretKey
     * @throws InvalidBeemSenderName
     */
    public function __construct()
    {
        if (config('beem.api_key') === null || config('beem.api_key') === '') {
            throw new InvalidBeemApiKey();
        }

        if (config('beem.secret_key') === null || config('beem.secret_key') === '') {
            throw new InvalidBeemSecretKey();
        }

        if (config('beem.sender_name') === null || config('beem.sender_name') === '') {
            throw new InvalidBeemSenderName();
        }

        $this->apiKey = config('beem.api_key');
        $this->secretKey = config('beem.secret_key');
        $this->senderName = config('beem.sender_name');
        $this->url = 'https://apisms.beem.africa/v1/send';
    }

    /**
     * @param  string  $apiKey
     * @return $this
     */
    public function apiKey(string $apiKey = ''): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @param  string  $secretKey
     * @return $this
     */
    public function secretKey(string $secretKey = ''): static
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function send(): ResponseInterface
    {
        $client = new Client();

        return $client->post(
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
     * @param  mixed  $collection
     * @param  string  $column
     * @return $this
     *
     * @throws Exception
     */
    public function loadRecipients(mixed $collection, string $column = 'phone_number'): static
    {
        $recipients = $collection->map(fn ($item) => $item[$column])->toArray();

        return $this->getRecipients($recipients);
    }

    /**
     * @param  array  $recipients
     * @return $this
     *
     * @throws Exception
     */
    public function getRecipients(array $recipients): static
    {
        if (count($recipients)) {
            throw new Exception('Recipients should not be empty');
        }

        $this->validateRecipientAddresses($recipients);

        $this->recipientAddress = $this->formatRecipientAddress($recipients);

        return $this;
    }

    /**
     * @param  array  $recipients
     * @return void
     */
    protected function validateRecipientAddresses(array $recipients): void
    {
        if (config('beem.validate_phone_addresses')) {
            app(Validator::class)->validate($recipients);
        }
    }

    /**
     * @param  array  $recipients
     * @return array
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
                ['recipient_id' => rand(00000000, 999999999)],
                $singleRecipient
            );
        }

        return $recipientAddress;
    }

    /**
     * @param  string  $message
     * @return $this
     */
    public function content(string $message = ''): static
    {
        $this->message = $message;

        return $this;
    }
}
