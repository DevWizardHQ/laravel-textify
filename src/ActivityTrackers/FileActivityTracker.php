<?php

declare(strict_types=1);

namespace DevWizard\Textify\ActivityTrackers;

use DevWizard\Textify\Contracts\ActivityTrackerInterface;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

class FileActivityTracker implements ActivityTrackerInterface
{
    private string $logFile;

    public function __construct()
    {
        $this->logFile = storage_path('logs/textify-activities.log');
    }

    public function trackSending(TextifyMessage $message, string $provider): void
    {
        $this->writeActivity([
            'timestamp' => now()->toISOString(),
            'message_id' => $message->getId(),
            'provider' => $provider,
            'to' => $message->getTo(),
            'from' => $message->getFrom(),
            'message_length' => strlen($message->getMessage()),
            'status' => 'sending',
            'success' => false,
            'metadata' => $message->getMetadata(),
        ]);
    }

    public function trackSent(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        $this->writeActivity([
            'timestamp' => now()->toISOString(),
            'message_id' => $message->getId(),
            'provider_message_id' => $response->getMessageId(),
            'provider' => $provider,
            'to' => $message->getTo(),
            'from' => $message->getFrom(),
            'status' => 'sent',
            'success' => true,
            'cost' => $response->getCost(),
            'metadata' => [...$message->getMetadata(), ...$response->getMetadata()],
        ]);
    }

    public function trackFailed(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        $this->writeActivity([
            'timestamp' => now()->toISOString(),
            'message_id' => $message->getId(),
            'provider' => $provider,
            'to' => $message->getTo(),
            'from' => $message->getFrom(),
            'status' => 'failed',
            'success' => false,
            'error_code' => $response->getErrorCode(),
            'error_message' => $response->getErrorMessage(),
            'metadata' => [...$message->getMetadata(), ...$response->getMetadata()],
        ]);
    }

    public function updateStatus(string $messageId, string $status, array $metadata = []): void
    {
        $this->writeActivity([
            'timestamp' => now()->toISOString(),
            'message_id' => $messageId,
            'status' => $status,
            'action' => 'status_update',
            'metadata' => $metadata,
        ]);
    }

    public function getStats(array $filters = []): array
    {
        return [
            'total_sent' => 0,
            'total_failed' => 0,
            'total_cost' => 0,
            'by_provider' => [],
            'by_status' => [],
            'message' => 'Stats not available for file-based activity tracking. Use database tracker for analytics.',
        ];
    }

    public function getActivities(array $filters = [], int $limit = 100): array
    {
        // File logger cannot easily retrieve activities, return empty array
        return [];
    }

    private function writeActivity(array $data): void
    {
        try {
            $logEntry = json_encode($data).PHP_EOL;
            file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            // Silently fail - don't break SMS sending if activity tracking fails
            logger()->error('Failed to write SMS activity to file', [
                'file' => $this->logFile,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
