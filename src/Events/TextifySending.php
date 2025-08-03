<?php

declare(strict_types=1);

namespace DevWizard\Textify\Events;

use DevWizard\Textify\DTOs\TextifyMessage;

class TextifySending
{
    public function __construct(
        public readonly TextifyMessage $message,
        public readonly string $provider
    ) {}
}
