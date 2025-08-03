<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use Illuminate\Support\Facades\Log;

class LogProvider extends BaseProvider
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'log';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateConfig(): void
    {
        // No validation required for log provider
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
        $channel = $this->config['channel'] ?? 'single';

        Log::channel($channel)->info('SMS would be sent', [
            'to' => $message->to,
            'from' => $message->from,
            'message' => $message->message,
            'metadata' => $message->metadata,
        ]);

        return [
            'status' => 'logged',
            'message_id' => 'log_'.uniqid(),
            'to' => $message->to,
            'message' => $message->message,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function parseResponse(array $response): TextifyResponse
    {
        return TextifyResponse::success(
            messageId: $response['message_id'],
            status: 'logged',
            rawResponse: $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryStatus(string $messageId): string
    {
        return 'logged';
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance(): float
    {
        return 999.99; // Mock balance for log provider
    }

    /**
     * {@inheritdoc}
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Accept any phone number for log provider
        return ! empty(trim($phoneNumber));
    }

    /**
     * {@inheritdoc}
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        return $phoneNumber;
    }
}
