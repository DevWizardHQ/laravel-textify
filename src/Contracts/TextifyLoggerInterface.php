<?php

declare(strict_types=1);

namespace DevWizard\Textify\Contracts;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

interface TextifyLoggerInterface
{
    /**
     * Log Textify sending attempt
     */
    public function logSending(TextifyMessage $message, string $provider): void;

    /**
     * Log successful Textify
     */
    public function logSent(TextifyMessage $message, TextifyResponse $response, string $provider): void;

    /**
     * Log failed Textify
     */
    public function logFailed(TextifyMessage $message, TextifyResponse $response, string $provider): void;

    /**
     * Update Textify status
     */
    public function updateStatus(string $messageId, string $status, array $metadata = []): void;
}
