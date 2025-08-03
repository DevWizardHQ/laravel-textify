<?php

declare(strict_types=1);

namespace DevWizard\Textify\Exceptions;

use Exception;

class TextifyException extends Exception
{
    protected string $provider;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, string $provider = '')
    {
        parent::__construct($message, $code, $previous);
        $this->provider = $provider;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public static function providerNotFound(string $provider): self
    {
        return new self("Textify provider '{$provider}' not found.", 0, null, $provider);
    }

    public static function configurationMissing(string $key, string $provider): self
    {
        return new self("Missing configuration key '{$key}' for provider '{$provider}'.", 0, null, $provider);
    }

    public static function providerError(string $message, string $provider, ?\Throwable $previous = null): self
    {
        return new self($message, 0, $previous, $provider);
    }
}
