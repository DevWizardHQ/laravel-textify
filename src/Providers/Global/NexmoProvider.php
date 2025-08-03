<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Global;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Providers\BaseProvider;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class NexmoProvider extends BaseProvider
{
    protected string $name = 'nexmo';

    protected array $supportedCountries = []; // Supports all countries

    private Client $vonageClient;

    protected function getClientConfig(): array
    {
        // Return empty array since we're using Vonage client directly
        return [];
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['api_key', 'api_secret', 'from'];
    }

    protected function validateConfig(): void
    {
        $this->ensureConfigKeys();
        $this->initializeVonageClient();
    }

    /**
     * Initialize the Vonage client
     */
    private function initializeVonageClient(): void
    {
        if (! class_exists(Client::class)) {
            throw new TextifyException(
                'Vonage client not found. Please install it using: composer require vonage/client'
            );
        }

        try {
            // Use Basic authentication with API Key and Secret
            $basic = new Basic($this->config['api_key'], $this->config['api_secret']);
            $this->vonageClient = new Client($basic);
        } catch (\Exception $e) {
            throw new TextifyException('Failed to initialize Vonage client: '.$e->getMessage());
        }
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        try {
            $sms = new SMS(
                $message->getTo(),
                $message->getFrom() ?: $this->config['from'],
                $message->getMessage()
            );

            // Add optional client reference if configured
            if (isset($this->config['client_ref'])) {
                $sms->setClientRef($this->config['client_ref']);
            }

            // Send the SMS
            $collection = $this->vonageClient->sms()->send($sms);

            // Get the first message from the collection
            $response = $collection->current();

            return [
                'message_id' => $response->getMessageId(),
                'status' => $response->getStatus(),
                'remaining_balance' => $response->getRemainingBalance(),
                'message_price' => $response->getMessagePrice(),
                'network' => $response->getNetwork(),
                'to' => $response->getTo(),
                'message_count' => count($collection), // Total message parts
            ];
        } catch (\Exception $e) {
            throw new TextifyException('Vonage SMS sending failed: '.$e->getMessage());
        }
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        // Check for successful response with message_id
        if (isset($response['message_id'])) {
            $status = match ((int) $response['status']) {
                0 => 'delivered',
                1 => 'queued',
                2 => 'failed',
                3 => 'failed',
                4 => 'failed',
                5 => 'failed',
                6 => 'failed',
                7 => 'failed',
                8 => 'failed',
                9 => 'failed',
                10 => 'failed',
                11 => 'failed',
                12 => 'failed',
                13 => 'failed',
                14 => 'failed',
                15 => 'failed',
                default => 'unknown'
            };

            return TextifyResponse::success(
                messageId: $response['message_id'],
                status: $status,
                cost: isset($response['message_price']) ? (float) $response['message_price'] : null,
                rawResponse: [
                    'provider' => $this->name,
                    'response' => $response,
                ]
            );
        }

        return TextifyResponse::failed(
            errorCode: 'invalid_response',
            errorMessage: 'Invalid response format from Vonage SMS API',
            rawResponse: [
                'provider' => $this->name,
                'response' => $response,
            ]
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDeliveryStatus(string $messageId): string
    {
        // Vonage SMS API doesn't have direct status query
        // Status is typically received via webhooks
        return 'pending';
    }

    public function getBalance(): float
    {
        try {
            $response = $this->vonageClient->account()->getBalance();

            return (float) $response->getBalance();
        } catch (\Exception $e) {
            throw new TextifyException('Failed to retrieve Vonage account balance: '.$e->getMessage());
        }
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Global providers expect international format with + prefix
        return preg_match('/^\+[1-9]\d{6,14}$/', $phoneNumber) === 1;
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-digit characters
        $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

        // Add + prefix for international format (consistent with global providers)
        return '+'.$phoneNumber;
    }
}
