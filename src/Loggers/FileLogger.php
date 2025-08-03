<?php

declare(strict_types=1);

namespace DevWizard\Textify\Loggers;

use DevWizard\Textify\Contracts\TextifyLoggerInterface;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use Illuminate\Support\Facades\Log;

class FileLogger implements TextifyLoggerInterface
{
    public function logSending(TextifyMessage $message, string $provider): void
    {
        Log::info('SMS Sending', [
            'message_id' => $message->getId(),
            'provider' => $provider,
            'to' => $message->getTo(),
            'from' => $message->getFrom(),
            'message_length' => strlen($message->getMessage()),
            'status' => 'sending',
            'metadata' => $message->getMetadata(),
        ]);
    }

    public function logSent(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        Log::info('SMS Sent Successfully', [
            'message_id' => $message->getId(),
            'provider_message_id' => $response->getMessageId(),
            'provider' => $provider,
            'to' => $message->getTo(),
            'from' => $message->getFrom(),
            'status' => 'sent',
            'cost' => $response->getCost(),
            'metadata' => [...$message->getMetadata(), ...$response->getMetadata()],
        ]);
    }

    public function logFailed(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        Log::error('SMS Failed', [
            'message_id' => $message->getId(),
            'provider' => $provider,
            'to' => $message->getTo(),
            'from' => $message->getFrom(),
            'status' => 'failed',
            'error_code' => $response->getErrorCode(),
            'error_message' => $response->getErrorMessage(),
            'metadata' => [...$message->getMetadata(), ...$response->getMetadata()],
        ]);
    }

    public function updateStatus(string $messageId, string $status, array $metadata = []): void
    {
        Log::info('SMS Status Updated', [
            'message_id' => $messageId,
            'status' => $status,
            'metadata' => $metadata,
        ]);
    }
}
