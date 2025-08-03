<?php

declare(strict_types=1);

namespace DevWizard\Textify\ActivityTrackers;

use DevWizard\Textify\Contracts\ActivityTrackerInterface;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

class NullActivityTracker implements ActivityTrackerInterface
{
    public function trackSending(TextifyMessage $message, string $provider): void
    {
        // Do nothing
    }

    public function trackSent(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        // Do nothing
    }

    public function trackFailed(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        // Do nothing
    }

    public function updateStatus(string $messageId, string $status, array $metadata = []): void
    {
        // Do nothing - activity tracking disabled
    }

    public function getStats(array $filters = []): array
    {
        return [
            'total_sent' => 0,
            'total_failed' => 0,
            'total_cost' => 0,
            'by_provider' => [],
            'by_status' => [],
            'message' => 'Activity tracking is disabled',
        ];
    }

    public function getActivities(array $filters = [], int $limit = 100): array
    {
        // Do nothing - activity tracking disabled
        return [];
    }
}
