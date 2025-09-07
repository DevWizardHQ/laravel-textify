<?php

declare(strict_types=1);

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\Providers\BaseProvider;
use DevWizard\Textify\DTOs\TextifyResponse;
use GuzzleHttp\Client;

/**
 * Mock provider for testing connect_timeout functionality
 */
class MockProvider extends BaseProvider
{
    protected function validateConfig(): void
    {
        // Mock validation - no requirements
    }

    protected function getRequiredConfigKeys(): array
    {
        return []; // No required keys for mock
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        return ['status' => 'success', 'message_id' => '12345'];
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        return new TextifyResponse(
            success: true,
            messageId: $response['message_id'] ?? null,
            rawResponse: $response
        );
    }

    public function getName(): string
    {
        return 'mock';
    }

    protected function getSupportedCountries(): array
    {
        return ['BD'];
    }

    public function getDeliveryStatus(string $messageId): string
    {
        return 'delivered';
    }

    public function getBalance(): float
    {
        return 100.0;
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        return true;
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        return $phoneNumber;
    }

    public function getClientConfigForTesting(): array
    {
        return $this->getClientConfig();
    }
}

it('includes connect_timeout in client configuration', function () {
    $config = [
        'timeout' => 25,
        'connect_timeout' => 15,
        'verify_ssl' => true,
    ];

    $provider = new MockProvider($config);
    $clientConfig = $provider->getClientConfigForTesting();

    expect($clientConfig)->toHaveKey('timeout', 25);
    expect($clientConfig)->toHaveKey('connect_timeout', 15);
    expect($clientConfig)->toHaveKey('verify', true);
});

it('uses default connect_timeout when not specified', function () {
    $config = [
        'timeout' => 25,
        'verify_ssl' => true,
    ];

    $provider = new MockProvider($config);
    $clientConfig = $provider->getClientConfigForTesting();

    expect($clientConfig)->toHaveKey('timeout', 25);
    expect($clientConfig)->toHaveKey('connect_timeout', 10); // Default value
    expect($clientConfig)->toHaveKey('verify', true);
});

it('uses default values when no configuration provided', function () {
    $config = [];

    $provider = new MockProvider($config);
    $clientConfig = $provider->getClientConfigForTesting();

    expect($clientConfig)->toHaveKey('timeout', 30); // Default value
    expect($clientConfig)->toHaveKey('connect_timeout', 10); // Default value
    expect($clientConfig)->toHaveKey('verify', false); // Default value
});
