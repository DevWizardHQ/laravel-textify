<?php

declare(strict_types=1);

namespace DevWizard\Textify\DTOs;

use Illuminate\Support\Str;

/**
 * TextifyMessage - Data Transfer Object for SMS messages
 *
 * This class represents an SMS message with all its properties including recipient,
 * content, sender, and metadata. It provides a consistent structure for SMS data
 * throughout the application and includes automatic ID generation for tracking.
 *
 * Key features:
 * - Immutable message data
 * - Automatic unique ID generation
 * - Metadata support for custom data
 * - Array conversion for serialization
 * - Type-safe property access
 */
class TextifyMessage
{
    /**
     * Unique message identifier
     */
    private readonly string $id;

    /**
     * Create a new TextifyMessage instance
     *
     * @param  string  $to  Recipient phone number
     * @param  string  $message  SMS message content
     * @param  string|null  $from  Sender ID or phone number (optional)
     * @param  array  $metadata  Additional metadata for the message (optional)
     */
    public function __construct(
        public readonly string $to,
        public readonly string $message,
        public readonly ?string $from = null,
        public readonly array $metadata = []
    ) {
        $this->id = $this->generateId();
    }

    /**
     * Get the unique message identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the recipient phone number
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * Get the SMS message content
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the sender ID or phone number
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * Get the message metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'to' => $this->to,
            'message' => $this->message,
            'from' => $this->from,
            'metadata' => $this->metadata,
        ];
    }

    public static function create(string $to, string $message, ?string $from = null, array $metadata = []): self
    {
        return new self($to, $message, $from, $metadata);
    }

    private function generateId(): string
    {
        return 'textify_'.Str::random(16).'_'.time();
    }
}
