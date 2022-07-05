<?php

namespace Emanate\BeemSms;

use Illuminate\Http\Client\ConnectionException;

trait HandlesApiInteraction
{
    /**
     * @param $debug
     * @param $credentials
     * @param $url
     * @param $payload
     * @return \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function send($debug = false, $credentials, $url, $payload)
    {
        $response = (new \Illuminate\Http\Client\Factory)->retry(3, 100, function ($exception) {
            return $exception instanceof ConnectionException;
        })
            ->withOptions([
                'debug' => $debug
            ])
            ->withHeaders([
                'Authorization:Basic ' . base64_encode($credentials),
                'Content-Type: application/json'
            ])->post($url, $payload)
            ->throw();

        return $response;
    }

    /**
     * @param $debug
     * @param $credentials
     * @param $url
     * @return void
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function balance($debug = false, $credentials, $url)
    {
        $response = (new \Illuminate\Http\Client\Factory)->retry(3, 100, function ($exception) {
            return $exception instanceof ConnectionException;
        })
            ->withOptions([
                'debug' => $debug
            ])
            ->withHeaders([
                'Authorization:Basic ' . base64_encode($credentials),
                'Content-Type: application/json'
            ])->post($url)
            ->throw();
    }
}
