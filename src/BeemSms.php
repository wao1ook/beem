<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class BeemSms
{
    const SENDING_SMS_URL = 'https://apisms.beem.africa/v1/send';

    const CHECK_BALANCE = 'https://apisms.beem.africa/public/v1/vendors/balance';

    public string $message;

    public string $recipients;

    /**
     * The Beem Sms configuration.
     *
     * @var array
     */
    protected array $config;

    /**
     * Create a new BeemSMS instance.
     *
     * @param array $config
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->config = config('beem-sms');
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

    /**
     * @param array $recipientAddress
     * @return BeemSms
     */
    public function recipients(array $recipientAddress = []): BeemSms
    {
        $recipient = [];

        foreach ($recipientAddress as $eachRecipient) {
            $recipient[] = array_fill_keys(['dest_addr'], $eachRecipient);
        }

        $recipients = [];

        foreach ($recipient as $singleRecipient) {
            $recipients[] = array_merge(
                ['recipient_id' => rand(00000000, 999999999)],
                $singleRecipient
            );
        }

        $this->recipients = json_encode($recipients);

        return $this;
    }


    public function send()
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post(
            BeemSms::SENDING_SMS_URL,
            [
                'verify' => false,
                'auth' => [config('beem-sms.api_key'), config('beem-sms.secret_key')],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'source_addr' => config('beem.sender_name'),
                    'message' => $this->message,
                    'encoding' => 0,
                    'recipients' => $this->recipients,
                ],
            ]
        );
    }

    /**
     * @return StreamInterface
     * @throws GuzzleException
     */
    public function balance(): StreamInterface
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post(
            BeemSms::CHECK_BALANCE,
            [
                'verify' => false,
                'auth' => [config('beem-sms.api_key'), config('beem-sms.secret_key')],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]
        );
    }
}



