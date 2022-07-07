<?php

namespace Emanate\BeemSms;

use Illuminate\Http\Client\RequestException;
use Illuminate\Notifications\Notification;

class BeemSmsChannel
{

    /**
     * The sender name phone number notifications should have.
     *
     * @var string
     */
    protected string $senderName;

    protected BeemSms $beemSms;

    /**
     * Create a new Beem channel instance.
     *
     * @param string $senderName
     * @param BeemSms $beemSms
     */
    public function __construct(string $senderName, BeemSms $beemSms)
    {
        $this->senderName = $senderName;
        $this->beemSms = $beemSms;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return void
     * @throws RequestException
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$recipients = $notifiable->routeNotificationFor('beem', $notification)) {
            return;
        }

        $message = $notification->toBeem($notifiable);

        if (is_string($message)) {
            $message = new BeemSmsMessage($message);
        }

        $payload = [
            "schedule_time" => "",
            'source_addr' => $message->senderName,
            'recipients' => $recipients,
            'message' => trim($message->content),
        ];

        $this->beemSms->fire($payload);
    }
}
