<?php

namespace Emanate\BeemSms\Contracts;


interface BeemSmsInterface
{

    public function send($debug = false, $credentials, $url, $payload);

    public function fire();

    public function balance($debug = false, $credentials, $url);

    public function loadAuthKey($apiKey, $secretKey): string;
}
