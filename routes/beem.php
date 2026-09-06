<?php

declare(strict_types=1);

use Emanate\BeemSms\Http\Controllers\InboundSmsController;
use Illuminate\Support\Facades\Route;

Route::post(config('beem.two_way.path', 'beem/inbound'), InboundSmsController::class)
    ->middleware(config('beem.two_way.middleware', []))
    ->name('beem.inbound');
