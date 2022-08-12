<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class BeemSms
{
    public string $message;

    public string $url = 'https://apisms.beem.africa/v1/send';

    public array $recipientAddress;

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
                'auth' => [config('beem.api_key'), config('beem.secret_key')],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'source_addr' => config('beem.sender_name'),
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
            $recipient[] = array_fill_keys(['dest_addr'], $eachRecipient);
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



