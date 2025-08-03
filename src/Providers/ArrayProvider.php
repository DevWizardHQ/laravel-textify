<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

class ArrayProvider extends BaseProvider
{
    protected static array $messages = [];

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'array';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateConfig(): void
    {
        // No validation required for array provider
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function sendRequest(TextifyMessage $message): array
    {
        $messageId = 'array_'.uniqid();

        $messageData = [
            'id' => $messageId,
            'to' => $message->to,
            'from' => $message->from,
            'message' => $message->message,
            'metadata' => $message->metadata,
            'status' => 'sent',
            'sent_at' => date('Y-m-d\TH:i:s\Z'),
        ];

        static::$messages[] = $messageData;

        return $messageData;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseResponse(array $response): TextifyResponse
    {
        return TextifyResponse::success(
            messageId: $response['id'],
            status: $response['status'],
            rawResponse: $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryStatus(string $messageId): string
    {
        foreach (static::$messages as $message) {
            if ($message['id'] === $messageId) {
                return $message['status'];
            }
        }

        return 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance(): float
    {
        return 999.99; // Mock balance for array provider
    }

    /**
     * {@inheritdoc}
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Accept any phone number for array provider
        return ! empty(trim($phoneNumber));
    }

    /**
     * {@inheritdoc}
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        return $phoneNumber;
    }

    /**
     * Get all stored messages
     */
    public static function getMessages(): array
    {
        return static::$messages;
    }

    /**
     * Clear all stored messages
     */
    public static function clearMessages(): void
    {
        static::$messages = [];
    }

    /**
     * Get message by ID
     */
    public static function getMessage(string $messageId): ?array
    {
        foreach (static::$messages as $message) {
            if ($message['id'] === $messageId) {
                return $message;
            }
        }

        return null;
    }
}
