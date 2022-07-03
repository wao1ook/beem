<?php

namespace Emanate\BeemSms;

class BeemSms
{
    public string $apiUrl;
    public string $apiKey;
    public string $secretKey;
    public string $senderName;

    public function __construct(array $config)
    {
        $this->apiUrl = $config['api_url'];
        $this->apiKey = $config['api_key'];
        $this->secretKey = $config['secret_key'];
        $this->senderName = $config['sender_name'];
    }

    public function sendMessage()
    {
        $curl = curl_init();

        $payload = [
            'source_addr' => $this->senderName,
            'encoding' => 0,
            'schedule_time' => '',
            'message' => 'Test SMS',
            'recipients' => [
                ['recipient_id' => '1', 'dest_addr' => '255656791558'],
            ],
        ];

        $ch = curl_init($this->apiUrl);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization:Basic ' . base64_encode("$this->apiKey:$this->secretKey"),
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);

        $results = json_decode($response, true, 512, 4194304);

        curl_close($curl);

        dd($results);
    }
}
