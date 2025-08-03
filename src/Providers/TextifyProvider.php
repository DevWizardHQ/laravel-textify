<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

/**
 * Simple base class for custom SMS providers
 * Users can extend this to create their own providers easily
 */
abstract class TextifyProvider extends BaseProvider
{
    /**
     * Override this method to implement your SMS sending logic
     */
    abstract protected function sendSms(TextifyMessage $message): array;

    /**
     * Override this method to parse your API response
     */
    abstract protected function parseApiResponse(array $response): TextifyResponse;

    /**
     * Override this method to return your provider's name
     */
    abstract public function getProviderName(): string;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->getProviderName();
    }

    /**
     * {@inheritdoc}
     */
    protected function sendRequest(TextifyMessage $message): array
    {
        return $this->sendSms($message);
    }

    /**
     * {@inheritdoc}
     */
    protected function parseResponse(array $response): TextifyResponse
    {
        return $this->parseApiResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateConfig(): void
    {
        // Override if you need custom validation
        $this->ensureConfigKeys();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        // Override to specify required config keys
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryStatus(string $messageId): string
    {
        // Override if your provider supports delivery status
        return 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance(): float
    {
        // Override if your provider supports balance check
        return 0.0;
    }

    /**
     * {@inheritdoc}
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Override for custom phone validation
        return ! empty(trim($phoneNumber));
    }

    /**
     * {@inheritdoc}
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Override for custom phone formatting
        return trim($phoneNumber);
    }
}
