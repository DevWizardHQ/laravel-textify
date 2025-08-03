<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Global;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Providers\BaseProvider;

/**
 * Placeholder provider for when Vonage client is not installed
 */
class NexmoPlaceholderProvider extends BaseProvider
{
    protected string $name = 'nexmo';

    protected array $supportedCountries = [];

    protected function getClientConfig(): array
    {
        return [];
    }

    protected function getRequiredConfigKeys(): array
    {
        return [];
    }

    protected function validateConfig(): void
    {
        throw new TextifyException(
            'Vonage client not found. Please install it using: composer require vonage/client'
        );
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        throw new TextifyException(
            'Vonage client not found. Please install it using: composer require vonage/client'
        );
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        throw new TextifyException(
            'Vonage client not found. Please install it using: composer require vonage/client'
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDeliveryStatus(string $messageId): string
    {
        throw new TextifyException(
            'Vonage client not found. Please install it using: composer require vonage/client'
        );
    }

    public function getBalance(): float
    {
        throw new TextifyException(
            'Vonage client not found. Please install it using: composer require vonage/client'
        );
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        throw new TextifyException(
            'Vonage client not found. Please install it using: composer require vonage/client'
        );
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        throw new TextifyException(
            'Vonage client not found. Please install it using: composer require vonage/client'
        );
    }
}
