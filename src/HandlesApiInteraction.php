<?php

namespace Emanate\BeemSms;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

trait HandlesApiInteraction
{
    /**
     * @param $debug
     * @param $credentials
     * @param $url
     * @param $payload
     * @return Response
     * @throws RequestException
     */
    public function sendSms($debug, $credentials, $url, $payload): Response
    {
        return Http::retry(3, 100, function ($exception) {
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
    }

    /**
     * @param $debug
     * @param $credentials
     * @param $url
     * @return Response
     * @throws RequestException
     */
    public function checkBalance($debug, $credentials, $url): Response
    {
        return Http::retry(3, 100, function ($exception) {
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
