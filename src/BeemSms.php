<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BeemSms
{
    public string $message;

    public string $url = 'https://apisms.beem.africa/v1/send';

    public array $recipientAddress;

    /**
     * @return void
     *
     * @throws GuzzleException
     */
    public function send(): void
    {
        $client = new Client();

        $response = $client->post(
            $this->url,
            [
                'verify' => false,
                'auth' => [config('beem-sms.api_key'), config('beem-sms.secret_key')],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'source_addr' => config('beem-sms.sender_name'),
                    'message' => $this->message,
                    'encoding' => 0,
                    'recipients' => $this->recipientAddress,
                ],
            ]
        );
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

        Log::debug('Recipients: ' . json_encode($recipientAddress));

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



