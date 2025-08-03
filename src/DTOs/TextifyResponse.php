<?php

declare(strict_types=1);

namespace DevWizard\Textify\DTOs;

/**
 * TextifyResponse - Data Transfer Object for SMS provider responses
 *
 * This class represents the response from an SMS provider after attempting to send
 * a message. It provides a standardized interface for handling both successful and
 * failed SMS attempts, including error information, provider-specific data, and
 * metadata like cost and delivery status.
 *
 * Key features:
 * - Immutable response data
 * - Success/failure status handling
 * - Provider-specific message IDs
 * - Error code and message handling
 * - Raw provider response storage
 * - Cost and status tracking
 * - Static factory methods for easy creation
 */
class TextifyResponse
{
    /**
     * Create a new TextifyResponse instance
     *
     * @param  bool  $success  Whether the SMS sending was successful
     * @param  string|null  $messageId  Provider-specific message identifier
     * @param  string|null  $errorMessage  Error message if sending failed
     * @param  string|null  $errorCode  Provider-specific error code
     * @param  array  $rawResponse  Raw response from the provider
     * @param  float|null  $cost  SMS sending cost (if available)
     * @param  string|null  $status  Current message status
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?string $messageId = null,
        public readonly ?string $errorMessage = null,
        public readonly ?string $errorCode = null,
        public readonly array $rawResponse = [],
        public readonly ?float $cost = null,
        public readonly ?string $status = null
    ) {}

    /**
     * Check if the SMS was sent successfully
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Check if the SMS sending failed
     */
    public function isFailed(): bool
    {
        return ! $this->success;
    }

    /**
     * Get the provider-specific message identifier
     */
    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getMetadata(): array
    {
        return $this->rawResponse;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'messageId' => $this->messageId,
            'errorMessage' => $this->errorMessage,
            'errorCode' => $this->errorCode,
            'rawResponse' => $this->rawResponse,
            'cost' => $this->cost,
            'status' => $this->status,
        ];
    }

    public static function success(
        ?string $messageId = null,
        ?float $cost = null,
        ?string $status = null,
        array $rawResponse = []
    ): self {
        return new self(
            success: true,
            messageId: $messageId,
            cost: $cost,
            status: $status,
            rawResponse: $rawResponse
        );
    }

    public static function failed(
        ?string $errorMessage = null,
        ?string $errorCode = null,
        array $rawResponse = []
    ): self {
        return new self(
            success: false,
            errorMessage: $errorMessage,
            errorCode: $errorCode,
            rawResponse: $rawResponse
        );
    }
}
