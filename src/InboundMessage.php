<?php

declare(strict_types=1);

namespace Emanate\BeemSms;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A mobile-originated message delivered to your callback URL by Beem.
 *
 * @see https://docs.beem.africa/guides/two-way-sms/inbound-callbacks
 *
 * @implements Arrayable<string, mixed>
 */
class InboundMessage implements Arrayable
{
    /**
     * The raw callback payload, as received.
     *
     * @var array<string, mixed>
     */
    protected array $payload;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self($payload);
    }

    /**
     * The sender's MSISDN or alphanumeric address.
     */
    public function from(): ?string
    {
        return $this->string('from');
    }

    /**
     * Your short code or long number that received the message.
     */
    public function to(): ?string
    {
        return $this->string('to');
    }

    /**
     * The channel the message arrived on, for example `sms`.
     */
    public function channel(): ?string
    {
        return $this->string('channel');
    }

    /**
     * Beem's transaction ID. Use it to process callbacks idempotently.
     */
    public function transactionId(): ?string
    {
        return $this->string('transaction_id');
    }

    /**
     * The time the message was received, as sent by Beem.
     */
    public function timeUtc(): ?string
    {
        return $this->string('timeUTC');
    }

    /**
     * The message body.
     */
    public function text(): ?string
    {
        return $this->string('message.text');
    }

    /**
     * The first word of the message body, which Beem treats as the keyword.
     */
    public function keyword(): ?string
    {
        // Not trim(), which Pint's mb_str_functions rule rewrites to the PHP 8.4-only mb_trim().
        $text = (string) preg_replace('/^\s+|\s+$/u', '', (string) $this->text());

        if ($text === '') {
            return null;
        }

        return explode(' ', $text)[0];
    }

    /**
     * URL of any media attached to the message.
     */
    public function mediaUrl(): ?string
    {
        $url = $this->string('message.media.mediaUrl');

        return $url === '' ? null : $url;
    }

    /**
     * Any custom object Beem attached to the message.
     *
     * @return array<string, mixed>
     */
    public function custom(): array
    {
        $custom = data_get($this->payload, 'message.custom');

        return is_array($custom) ? $custom : [];
    }

    /**
     * The billing block for this message.
     *
     * @return array<string, mixed>
     */
    public function billing(): array
    {
        $billing = data_get($this->payload, 'billing');

        return is_array($billing) ? $billing : [];
    }

    /**
     * The billing currency, for example `TZS`.
     */
    public function currency(): ?string
    {
        return $this->string('billing.currency');
    }

    /**
     * The price charged to the subscriber.
     */
    public function subscriberPrice(): ?string
    {
        return $this->string('billing.subscriber_price');
    }

    /**
     * The price billed to your account.
     */
    public function billingPrice(): ?string
    {
        return $this->string('billing.billing_price');
    }

    /**
     * The acknowledgement body Beem expects in response.
     *
     * @return array<string, mixed>
     */
    public function acknowledgement(): array
    {
        return [
            'transaction_id' => $this->transactionId(),
            'successful' => true,
        ];
    }

    /**
     * The raw callback payload, as received.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload;
    }

    /**
     * Read a dot-notated value from the payload as a string.
     */
    protected function string(string $key): ?string
    {
        $value = data_get($this->payload, $key);

        return is_scalar($value) ? (string) $value : null;
    }
}
