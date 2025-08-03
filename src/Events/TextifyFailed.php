<?php

declare(strict_types=1);

namespace DevWizard\Textify\Events;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

class TextifyFailed
{
    public function __construct(
        public readonly TextifyMessage $message,
        public readonly TextifyResponse $response,
        public readonly string $provider,
        public readonly ?\Throwable $exception = null
    ) {}
}
