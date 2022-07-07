<?php

namespace Emanate\BeemSms;


class BeemSmsMessage
{
    /**
     * The message content.
     *
     * @var string
     */
    public string $content;

    /**
     * The sender name the message should have.
     *
     * @var string
     */
    public string $senderName;

    /**
     * The custom Beem Sms instance.
     *
     * @var \Emanate\BeemSms\|null
     */
    public $beemSms;

    /**
     * Create a new message instance.
     *
     * @param string $content
     * @return void
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Set the message content.
     *
     * @param string $content
     * @return $this
     */
    public function content(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the sender name the message should have.
     *
     * @param string $senderName
     * @return $this
     */
    public function senderName(string $senderName)
    {
        $this->senderName = $senderName;

        return $this;
    }
}
