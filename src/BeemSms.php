<?php

namespace Emanate\BeemSms;

use Illuminate\Http\Client;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use RuntimeException;
use Emanate\BeemSms\HandlesApiInteraction;

class BeemSms
{
    use HandlesApiInteraction;

    const SENDING_SMS_URL = 'https://apisms.beem.africa/v1/send';

    const CHECK_BALANCE = 'https://apisms.beem.africa/public/v1/vendors/balance';
//    public string $apiKey;
//    public string $secretKey;
//    public string $senderName;

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
        $this->config = $config;
    }

    /**
     * Send an SMS.
     *
     * @return Client\Response
     *
     * @throws RuntimeException|Client\RequestException
     */
    public function fire($payload): Client\Response
    {
        $debug = $this->config['debug'];

        $credentials = $this->credentials();

        return $this->sendSms($debug, $credentials, BeemSms::SENDING_SMS_URL, $payload);
    }

    /**
     * @return string
     */
    public function credentials(): string
    {
        if ($this->config['api_key'] === null) {
            throw new RuntimeException('You must provide a Beem.API_KEY.');
        }

        $apiKey = $this->config['api_key'];

        if ($this->config['secret_key'] === null) {
            throw new RuntimeException('You must provide a Beem.SECRET_KEY.');
        }

        $secretKey = $this->config['secret_key'];

        if ($apiKey && $secretKey) {
            return $this->loadAuthKey(apiKey: $apiKey, secretKey: $secretKey);
        } else {
            $combinations = [
                'api_key + secret_key',
                'api_key + secret_key + sender_name',
            ];

            throw new RuntimeException(
                'Please provide your Beem API credentials. Possible combinations: '
                . join(', ', $combinations)
            );
        }
    }

    /**
     * Load the private key contents from the root directory of the application.
     *
     * @param $apiKey
     * @param $secretKey
     * @return string
     */
    public function loadAuthKey($apiKey, $secretKey): string
    {
        return "{$apiKey}:{$secretKey}";
    }

    /**
     * @return Client\Response
     * @throws Client\RequestException
     */
    public function balance(): Client\Response
    {
        $debug = $this->config['debug'];

        $credentials = $this->credentials();

        return $this->checkBalance($debug, $credentials, BeemSms::CHECK_BALANCE);
    }
}



