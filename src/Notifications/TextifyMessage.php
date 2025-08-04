<?php

declare(strict_types=1);

namespace DevWizard\Textify\Notifications;

/**
 * Textify Message for Notifications
 *
 * This class represents an SMS message data structure for Laravel notifications.
 * It provides a convenient way to structure SMS data in notification classes.
 */
class TextifyMessage
{
    public function __construct(
        public readonly string $message,
        public readonly ?string $from = null,
        public readonly ?string $driver = null,
        public readonly array $metadata = []
    ) {}

    /**
     * Create a new Textify message instance
     *
     * @param  string  $message  The SMS message content
     * @param  string|null  $from  The sender ID (optional)
     * @param  string|null  $driver  The SMS provider to use (optional)
     * @param  array  $metadata  Additional metadata (optional)
     * @return static
     */
    public static function create(
        string $message,
        ?string $from = null,
        ?string $driver = null,
        array $metadata = []
    ): static {
        return new static($message, $from, $driver, $metadata);
    }

    /**
     * Set the SMS message content
     *
     * @param  string  $message
     * @return static
     */
    public function message(string $message): static
    {
        return new static($message, $this->from, $this->driver, $this->metadata);
    }

    /**
     * Set the sender ID
     *
     * @param  string  $from
     * @return static
     */
    public function from(string $from): static
    {
        return new static($this->message, $from, $this->driver, $this->metadata);
    }

    /**
     * Set the SMS provider/driver
     *
     * @param  string  $driver
     * @return static
     */
    public function driver(string $driver): static
    {
        return new static($this->message, $this->from, $driver, $this->metadata);
    }

    /**
     * Set metadata
     *
     * @param  array  $metadata
     * @return static
     */
    public function metadata(array $metadata): static
    {
        return new static($this->message, $this->from, $this->driver, $metadata);
    }

    /**
     * Get the message content
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the sender ID
     *
     * @return string|null
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * Get the driver/provider
     *
     * @return string|null
     */
    public function getDriver(): ?string
    {
        return $this->driver;
    }

    /**
     * Get the metadata
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'from' => $this->from,
            'driver' => $this->driver,
            'metadata' => $this->metadata,
        ];
    }
}
