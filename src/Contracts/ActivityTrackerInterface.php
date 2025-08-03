<?php

declare(strict_types=1);

namespace DevWizard\Textify\Contracts;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

interface ActivityTrackerInterface
{
    /**
     * Track Textify sending attempt
     */
    public function trackSending(TextifyMessage $message, string $provider): void;

    /**
     * Track successful Textify
     */
    public function trackSent(TextifyMessage $message, TextifyResponse $response, string $provider): void;

    /**
     * Track failed Textify
     */
    public function trackFailed(TextifyMessage $message, TextifyResponse $response, string $provider): void;

    /**
     * Update Textify status
     */
    public function updateStatus(string $messageId, string $status, array $metadata = []): void;

    /**
     * Get activities with optional filters
     */
    public function getActivities(array $filters = [], int $limit = 100): array;

    /**
     * Get activity statistics
     */
    public function getStats(array $filters = []): array;
}
