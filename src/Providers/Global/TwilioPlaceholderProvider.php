<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Global;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Providers\BaseProvider;

class TwilioPlaceholderProvider extends BaseProvider
{
    protected string $name = 'twilio';

    protected function getClientConfig(): array
    {
        return [];
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['account_sid', 'auth_token', 'from'];
    }

    protected function validateConfig(): void
    {
        // No validation needed for placeholder
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        throw new TextifyException(
            'Twilio SDK is not installed. Please install it using: composer require twilio/sdk'
        );
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        throw new TextifyException(
            'Twilio SDK is not installed. Please install it using: composer require twilio/sdk'
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDeliveryStatus(string $messageId): string
    {
        throw new TextifyException(
            'Twilio SDK is not installed. Please install it using: composer require twilio/sdk'
        );
    }

    public function getBalance(): float
    {
        throw new TextifyException(
            'Twilio SDK is not installed. Please install it using: composer require twilio/sdk'
        );
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Basic validation even without SDK
        return preg_match('/^\+[1-9]\d{6,14}$/', $phoneNumber) === 1;
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Basic formatting even without SDK
        $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

        return '+'.$phoneNumber;
    }
}
