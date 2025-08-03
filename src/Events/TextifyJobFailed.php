<?php

declare(strict_types=1);

namespace DevWizard\Textify\Events;

use DevWizard\Textify\DTOs\TextifyMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a queued Textify job fails
 *
 * This event is dispatched when a SendTextifyJob fails to process,
 * allowing applications to handle job failures with custom logic
 * such as retries, notifications, or logging.
 */
class TextifyJobFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  TextifyMessage  $message  The SMS message that failed to send
     * @param  string  $provider  The provider that was being used
     * @param  \Throwable  $exception  The exception that caused the failure
     */
    public function __construct(
        public readonly TextifyMessage $message,
        public readonly string $provider,
        public readonly \Throwable $exception
    ) {}

    /**
     * Get the message that failed to send
     */
    public function getMessage(): TextifyMessage
    {
        return $this->message;
    }

    /**
     * Get the provider that was being used
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Get the exception that caused the failure
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }

    /**
     * Get the recipient phone number
     */
    public function getRecipient(): string
    {
        return $this->message->getTo();
    }

    /**
     * Get the message content
     */
    public function getMessageContent(): string
    {
        return $this->message->getMessage();
    }

    /**
     * Get the sender ID
     */
    public function getSender(): ?string
    {
        return $this->message->getFrom();
    }

    /**
     * Get the error message
     */
    public function getErrorMessage(): string
    {
        return $this->exception->getMessage();
    }

    /**
     * Get the error code if available
     */
    public function getErrorCode(): ?string
    {
        return $this->exception->getCode() ? (string) $this->exception->getCode() : null;
    }

    /**
     * Get metadata for logging or debugging
     */
    public function getMetadata(): array
    {
        return [
            'message_id' => $this->message->getId(),
            'to' => $this->message->getTo(),
            'from' => $this->message->getFrom(),
            'provider' => $this->provider,
            'error_message' => $this->exception->getMessage(),
            'error_code' => $this->exception->getCode(),
            'error_file' => $this->exception->getFile(),
            'error_line' => $this->exception->getLine(),
            'timestamp' => now()->toISOString(),
        ];
    }
}
