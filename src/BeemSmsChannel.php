<?php

namespace Emanate\BeemSms;

use Illuminate\Notifications\Notification;

class BeemSmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toBeemSMS($notifiable);

//        $message = new BeemSms($)
    }
}
