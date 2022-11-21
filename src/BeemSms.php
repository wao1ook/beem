<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use Emanate\BeemSms\Exceptions\InvalidBeemApiKeyException;
use Emanate\BeemSms\Exceptions\InvalidBeemSecretKeyException;
use Emanate\BeemSms\Exceptions\InvalidBeemSenderNameException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class BeemSms
{
    /**
     * @var string
     */
    protected string $apiKey;

    /**
     * @var string
     */
    protected string $secretKey;

    /**
     * @var string
     */
    protected string $senderName;

    /**
     * @var string
     */
    public string $message;

    /**
     * @var string
     */
    public string $url;

    /**
     * @var array
     */
    public array $recipientAddress;


    /**
     * @throws InvalidBeemApiKeyException
     * @throws InvalidBeemSecretKeyException
     * @throws InvalidBeemSenderNameException
     */
    public function __construct()
    {
        if (config('beem.api_key') === null || config('beem.api_key') === '') throw new InvalidBeemApiKeyException;
        if (config('beem.secret_key') === null || config('beem.secret_key') === '') throw new InvalidBeemSecretKeyException();
        if (config('beem.sender_name') === null || config('beem.sender_name') === '') throw new InvalidBeemSenderNameException();

        $this->apiKey = config('beem.api_key');
        $this->secretKey = config('beem.secret_key');
        $this->senderName = config('beem.sender_name');
        $this->url = 'https://apisms.beem.africa/v1/send';
    }

    /**
     * @param string $apiKey
     * @return $this
     */
    public function apiKey(string $apiKey = ''): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @param string $secretKey
     * @return BeemSms
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
     * @param mixed $collection
     * @param string $column
     * @return BeemSms
     */
    public function loadRecipients(mixed $collection, string $column = 'phone_number'): static
    {
        $recipients = $collection->map(fn($item) => $item[$column])->toArray();

        return $this->getRecipients($recipients);
    }

    /**
     * @param array $recipients
     * @return $this
     */
    public function getRecipients(array $recipients = []): static
    {
        $recipient = [];

        foreach ($recipients as $eachRecipient) {
            if (str_starts_with($eachRecipient, '0')) {
                $recipient[] = array_fill_keys(['dest_addr'], substr_replace($eachRecipient, '+255', 0, 1));
            } else {
                $recipient[] = array_fill_keys(['dest_addr'], $eachRecipient);
            }
        }

        $recipientAddress = [];

        foreach ($recipient as $singleRecipient) {
            $recipientAddress[] = array_merge(
                ['recipient_id' => rand(00000000, 999999999)],
                $singleRecipient
            );
        }

        $this->recipientAddress = $recipientAddress;

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function content(string $message = ''): static
    {
        $this->message = $message;

        return $this;
    }
}



