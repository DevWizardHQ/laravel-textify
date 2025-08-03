<?php

declare(strict_types=1);

namespace DevWizard\Textify\Contracts;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

interface TextifyProviderInterface
{
    /**
     * Send a single Textify message
     */
    public function send(TextifyMessage $message): TextifyResponse;

    /**
     * Send bulk Textify messages
     */
    public function sendBulk(array $messages): array;

    /**
     * Get Textify delivery status
     */
    public function getDeliveryStatus(string $messageId): string;

    /**
     * Get account balance
     */
    public function getBalance(): float;

    /**
     * Get provider name
     */
    public function getName(): string;

    /**
     * Check if provider supports the given country code
     */
    public function supportsCountry(string $countryCode): bool;

    /**
     * Validate phone number format for this provider
     */
    public function validatePhoneNumber(string $phoneNumber): bool;

    /**
     * Format phone number according to provider requirements
     */
    public function formatPhoneNumber(string $phoneNumber): string;

    /**
     * Get provider configuration
     */
    public function getConfig(): array;

    /**
     * Set provider configuration
     */
    public function setConfig(array $config): void;
}
