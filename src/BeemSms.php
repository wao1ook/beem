<?php

namespace Emanate\BeemSms;

use Emanate\BeemSms\Contracts\BeemSmsInterface;
use Illuminate\Http\Client;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use RuntimeException;
use Emanate\BeemSms\HandlesApiInteraction;

class BeemSms implements BeemSmsInterface
{
    use HandlesApiInteraction;

    const SENDING_SMS_URL = 'https://apisms.beem.africa/v1/send';
    public string $apiKey;
    public string $secretKey;
    public string $senderName;

    /**
     * The Vonage configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * The HttpClient instance, if provided.
     *
     * @var \Psr\Http\Client\ClientInterface
     */
    protected $client;

    /**
     * Create a new Vonage instance.
     *
     * @param  array  $config
     * @param  \Psr\Http\Client\ClientInterface|null  $client
     * @return void
     */
    public function __construct(array $config = [], ?ClientInterface $client = null)
    {
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * Create a new Vonage Client.
     *
     * @return Client\Response
     *
     * @throws \RuntimeException
     */
    public function fire()
    {
        if ($this->config['api_key'] === null) {
                throw new RuntimeException('You must provide a Beem.API_KEY.');
        }

        $apiKey = $this->config['api_key'];

        if ($this->config['secret_key'] === null) {
            throw new RuntimeException('You must provide a Beem.SECRET_KEY.');
        }

        $secretKey = $this->config['secret_key'];

        $signatureCredentials = null;

        if ($apiKey && $secretKey) {
            $credentials = $this->loadAuthKey(apiKey: $apiKey, secretKey: $secretKey);
        } else {
            $combinations = [
                'api_key + secret_key',
                'api_key + secret_key + sender_name',
            ];

            throw new RuntimeException(
                'Please provide your Beem API credentials. Possible combinations: '
                .join(', ', $combinations)
            );
        }

        $payload = [
            'source_addr' => $this->senderName,
            'encoding' => 0,
            'schedule_time' => '',
            'message' => 'Test SMS',
            'recipients' => [
                ['recipient_id' => '1', 'dest_addr' => '255656791558'],
            ],
        ];

        $response = $this->send($debug = true, $credentials, BeemSms::SENDING_SMS_URL, $payload);

        return $response;
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

//    public function __construct(array $config = [])
//    {
//
//    }
//
//
//
//    public static function sendMessage()
//    {
////        $curl = curl_init();
//
////        $payload = [
////            'source_addr' => $this->senderName,
////            'encoding' => 0,
////            'schedule_time' => '',
////            'message' => 'Test SMS',
////            'recipients' => [
////                ['recipient_id' => '1', 'dest_addr' => '255656791558'],
////            ],
////        ];
//
//
//
////        $ch = curl_init(BeemSms::SENDING_SMS_URL);
////        error_reporting(E_ALL);
////        ini_set('display_errors', 1);
////        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
////        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
////        curl_setopt_array($ch, [
////            CURLOPT_POST => true,
////            CURLOPT_RETURNTRANSFER => true,
////            CURLOPT_HTTPHEADER => [
////                'Authorization:Basic ' . base64_encode("$this->apiKey:$this->secretKey"),
////                'Content-Type: application/json',
////            ],
////            CURLOPT_POSTFIELDS => json_encode($payload),
////        ]);
////
////        $response = curl_exec($ch);
////
////        $results = json_decode($response, true, 512, 4194304);
////
////        curl_close($curl);
//
//
////        dd($results);
//
//        $payload = [
//            'source_addr' => 'INFO',
//            'encoding' => 0,
//            'schedule_time' => '',
//            'message' => 'Test SMS',
//            'recipients' => [
//                ['recipient_id' => '1', 'dest_addr' => '255692107171'],
//            ],
//        ];
//
//        $response = (new \Illuminate\Http\Client\Factory)->retry(3, 100, function ($exception, $request) {
//            return $exception instanceof ConnectionException;
//        })
//            ->withOptions([
//                'debug' => true
//            ])
//            ->withHeaders([
//                'Authorization:Basic ' . base64_encode("5323bfaa612d2381:M2U2M2U2ODgwYzVhZTU1ZjBkNzU4OTExZWY0NWZiYjFmZjZiNDRjNGFmMjEyMmEwMGE3YWM1NGIzNzdmYjAwNg=="),
//                'Content-Type: application/json'
//            ])->post('https://apisms.beem.africa/v1/send', $payload)
//            ->throw();
//
//        print_r($response);
//    }



}



