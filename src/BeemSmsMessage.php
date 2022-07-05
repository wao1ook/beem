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
     * The phone number the message should be sent from.
     *
     * @var string
     */
    public string $from;

    /**
     * The message type.
     *
     * @var string
     */
    public string $type = 'text';

    /**
     * The custom Vonage client instance.
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
     * Set the phone number the message should be sent from.
     *
     * @param string $from
     * @return $this
     */
    public function from(string $from)
    {
        $this->from = $from;

        return $this;
    }
}
