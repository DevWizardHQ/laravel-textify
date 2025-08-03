<?php

declare(strict_types=1);

namespace DevWizard\Textify\Loggers;

use DevWizard\Textify\Contracts\TextifyLoggerInterface;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

class NullLogger implements TextifyLoggerInterface
{
    public function logSending(TextifyMessage $message, string $provider): void
    {
        // Do nothing
    }

    public function logSent(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        // Do nothing
    }

    public function logFailed(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        // Do nothing
    }

    public function updateStatus(string $messageId, string $status, array $metadata = []): void
    {
        // Do nothing
    }
}
