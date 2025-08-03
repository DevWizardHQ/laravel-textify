<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Global;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Providers\BaseProvider;

class TwilioProvider extends BaseProvider
{
    protected string $name = 'twilio';

    protected array $supportedCountries = []; // Supports all countries

    private $twilioClient;

    protected function getClientConfig(): array
    {
        // Return empty array since we're using Twilio client directly
        return [];
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['account_sid', 'auth_token', 'from'];
    }

    protected function validateConfig(): void
    {
        $this->ensureConfigKeys();
        $this->initializeTwilioClient();
    }

    /**
     * Initialize the Twilio client
     */
    private function initializeTwilioClient(): void
    {
        if (! class_exists('Twilio\Rest\Client')) {
            throw new TextifyException(
                'Twilio client not found. Please install it using: composer require twilio/sdk'
            );
        }

        try {
            $this->twilioClient = new \Twilio\Rest\Client(
                $this->config['account_sid'],
                $this->config['auth_token']
            );
        } catch (\Exception $e) {
            throw new TextifyException('Failed to initialize Twilio client: '.$e->getMessage());
        }
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        try {
            $messageInstance = $this->twilioClient->messages->create(
                $message->getTo(), // to
                [
                    'from' => $message->getFrom() ?: $this->config['from'],
                    'body' => $message->getMessage(),
                ]
            );

            return [
                'sid' => $messageInstance->sid,
                'status' => $messageInstance->status,
                'to' => $messageInstance->to,
                'from' => $messageInstance->from,
                'body' => $messageInstance->body,
                'price' => $messageInstance->price,
                'price_unit' => $messageInstance->priceUnit,
                'uri' => $messageInstance->uri,
                'date_created' => $messageInstance->dateCreated ? $messageInstance->dateCreated->format('Y-m-d H:i:s') : null,
                'date_updated' => $messageInstance->dateUpdated ? $messageInstance->dateUpdated->format('Y-m-d H:i:s') : null,
                'date_sent' => $messageInstance->dateSent ? $messageInstance->dateSent->format('Y-m-d H:i:s') : null,
                'error_code' => $messageInstance->errorCode,
                'error_message' => $messageInstance->errorMessage,
                'num_segments' => $messageInstance->numSegments,
                'direction' => $messageInstance->direction,
            ];
        } catch (\Exception $e) {
            throw new TextifyException('Twilio SMS sending failed: '.$e->getMessage());
        }
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        // Check for successful response with SID
        if (isset($response['sid'])) {
            $status = match ($response['status']) {
                'accepted', 'queued', 'sending' => 'queued',
                'sent', 'delivered' => 'delivered',
                'failed', 'undelivered' => 'failed',
                default => 'unknown'
            };

            // Check if there's an error
            if (! empty($response['error_code']) || ! empty($response['error_message'])) {
                return TextifyResponse::failed(
                    errorCode: (string) ($response['error_code'] ?? 'TWILIO_ERROR'),
                    errorMessage: $response['error_message'] ?? 'Unknown Twilio error',
                    rawResponse: [
                        'provider' => $this->name,
                        'response' => $response,
                    ]
                );
            }

            return TextifyResponse::success(
                messageId: $response['sid'],
                status: $status,
                cost: isset($response['price']) ? abs((float) $response['price']) : null,
                rawResponse: [
                    'provider' => $this->name,
                    'response' => $response,
                ]
            );
        }

        return TextifyResponse::failed(
            errorCode: 'invalid_response',
            errorMessage: 'Invalid response format from Twilio API',
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
        try {
            $message = $this->twilioClient->messages($messageId)->fetch();

            return $message->status;
        } catch (\Exception $e) {
            throw new TextifyException('Failed to get delivery status: '.$e->getMessage());
        }
    }

    public function getBalance(): float
    {
        try {
            $balance = $this->twilioClient->api->v2010->balance->fetch();

            return (float) $balance->balance;
        } catch (\Exception $e) {
            throw new TextifyException('Failed to retrieve Twilio account balance: '.$e->getMessage());
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
